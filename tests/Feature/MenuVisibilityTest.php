<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use Database\Seeders\MenuItemSeeder;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Cache navigasi bersifat statis; dibersihkan agar tiap uji berdiri sendiri.
        MenuItem::flushNavigationCache();
    }

    /**
     * Seeder menyiapkan seluruh menu bawaan pada konfigurasi MenuItem.
     */
    public function test_seeder_menyiapkan_menu_bawaan(): void
    {
        $this->seed(MenuItemSeeder::class);

        $this->assertDatabaseCount('menu_items', count(MenuItem::DEFAULTS));
        $this->assertDatabaseHas('menu_items', [
            'key' => 'program',
            'route_name' => 'program.index',
            'is_active' => true,
        ]);
    }

    /**
     * Menu wilayah memakai label "Wilayah" (bukan "Di Indonesia") agar sejalan
     * dengan judul bagian pada halaman beranda dan daftar wilayah.
     */
    public function test_menu_wilayah_memakai_label_wilayah(): void
    {
        $this->seed(MenuItemSeeder::class);

        $this->assertDatabaseHas('menu_items', [
            'key' => 'region',
            'label' => 'Wilayah',
            'route_name' => 'region.index',
            'pattern' => 'wilayah*',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Wilayah', false)
            ->assertDontSee('Di Indonesia', false);
    }

    /**
     * Seeder aman dijalankan ulang dan tidak menimpa perubahan pengelola.
     */
    public function test_seeder_aman_dijalankan_ulang(): void
    {
        $this->seed(MenuItemSeeder::class);

        MenuItem::query()->where('key', 'gallery')->update(['is_active' => false, 'label' => 'Album']);

        $this->seed(MenuItemSeeder::class);

        $this->assertDatabaseCount('menu_items', count(MenuItem::DEFAULTS));

        $gallery = MenuItem::query()->where('key', 'gallery')->firstOrFail();
        $this->assertFalse($gallery->is_active);
        $this->assertSame('Album', $gallery->label);
    }

    /**
     * Menu yang dimatikan tidak lagi tampil pada navigasi website. Selama masih
     * ada menu lain di kelompoknya, kelompoknya tetap tampil; kelompok yang
     * seluruh isinya dimatikan ikut hilang dari header.
     */
    public function test_menu_nonaktif_hilang_dari_navigasi_website(): void
    {
        $this->seed(MenuItemSeeder::class);

        $sebelum = $this->get('/')->assertOk();
        $this->assertSame(count(MenuItem::GROUPS), substr_count($sebelum->getContent(), 'site-nav-link'));
        $this->assertSame(7, substr_count($sebelum->getContent(), 'site-nav-dropdown-link'));

        MenuItem::query()->where('key', 'gallery')->update(['is_active' => false]);
        MenuItem::flushNavigationCache();

        // Isi "Publikasi" tinggal Berita: kelompoknya tetap ada.
        $sebagian = $this->get('/')->assertOk();
        $this->assertSame(count(MenuItem::GROUPS), substr_count($sebagian->getContent(), 'site-nav-link'));
        $this->assertSame(6, substr_count($sebagian->getContent(), 'site-nav-dropdown-link'));

        MenuItem::query()->where('key', 'news')->update(['is_active' => false]);
        MenuItem::flushNavigationCache();

        // Seluruh isi "Publikasi" dimatikan: kelompoknya ikut hilang.
        $sesudah = $this->get('/')->assertOk();
        $this->assertSame(count(MenuItem::GROUPS) - 1, substr_count($sesudah->getContent(), 'site-nav-link'));
        $this->assertSame(5, substr_count($sesudah->getContent(), 'site-nav-dropdown-link'));
    }

    /**
     * Saat tabel menu belum diisi, menu bawaan tetap dipakai agar navigasi
     * situs tidak hilang.
     */
    public function test_menu_bawaan_dipakai_saat_tabel_belum_diisi(): void
    {
        $menu = MenuItem::navigation();

        $this->assertCount(count(MenuItem::GROUPS), $menu);
        $this->assertSame('home', $menu[0]['route']);

        $this->get('/')->assertOk();
    }

    /**
     * Sembilan menu bawaan dirapikan menjadi lima menu utama: dua tautan tunggal
     * (Beranda dan Kontak) serta tiga kelompok yang tampil sebagai menu
     * tarik-turun (Profil, Program & Kegiatan, Publikasi).
     */
    public function test_menu_bawaan_dikelompokkan_menjadi_lima_menu_utama(): void
    {
        $this->seed(MenuItemSeeder::class);

        $menu = MenuItem::navigation();

        $this->assertSame(
            ['Beranda', 'Profil', 'Program & Kegiatan', 'Publikasi', 'Kontak'],
            array_column($menu, 'label'),
        );

        // Tautan tunggal: memakai nama menunya sendiri dan tanpa daftar isi.
        $this->assertSame('home', $menu[0]['route']);
        $this->assertSame([], $menu[0]['items']);
        $this->assertSame('contact', $menu[4]['route']);
        $this->assertSame([], $menu[4]['items']);

        // Kelompok tarik-turun: tanpa rute sendiri, isinya tetap seurutan.
        $this->assertNull($menu[1]['route']);
        $this->assertSame(['Tentang', 'Wilayah'], array_column($menu[1]['items'], 'label'));
        $this->assertSame(['Program', 'Kegiatan', 'Cerita'], array_column($menu[2]['items'], 'label'));
        $this->assertSame(['Berita', 'Galeri'], array_column($menu[3]['items'], 'label'));
    }

    /**
     * Halaman anggota membuat kelompoknya ikut ditandai aktif, sehingga
     * pengunjung tahu sedang berada di bagian mana (mis. Wilayah → Profil).
     */
    public function test_halaman_anggota_menandai_kelompok_menu_aktif(): void
    {
        $this->seed(MenuItemSeeder::class);

        $this->get('/wilayah')
            ->assertOk()
            ->assertSee('site-nav-trigger is-active', false)
            ->assertSee('site-nav-dropdown-link is-active', false);
    }

    /**
     * Menu tambahan dari panel (tidak tercantum pada GROUPS) tetap tampil
     * sebagai tautan tunggal agar tidak pernah hilang dari header.
     */
    public function test_menu_tambahan_dari_panel_tetap_tampil(): void
    {
        $this->seed(MenuItemSeeder::class);

        MenuItem::query()->create([
            'key' => 'panduan',
            'label' => 'Panduan Relawan',
            'route_name' => 'contact',
            'pattern' => 'kontak',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        MenuItem::flushNavigationCache();

        $menu = MenuItem::navigation();

        $this->assertCount(count(MenuItem::GROUPS) + 1, $menu);
        $this->assertSame('Panduan Relawan', end($menu)['label']);
        $this->assertSame([], end($menu)['items']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Panduan Relawan', false);

        $this->assertSame(
            count(MenuItem::GROUPS) + 1,
            substr_count($this->get('/')->getContent(), 'site-nav-link'),
        );
    }

    /**
     * Pengelola boleh menyembunyikan semua menu tanpa dipaksa kembali ke bawaan.
     */
    public function test_semua_menu_boleh_disembunyikan(): void
    {
        $this->seed(MenuItemSeeder::class);

        MenuItem::query()->update(['is_active' => false]);
        MenuItem::flushNavigationCache();

        $this->assertSame([], MenuItem::navigation());
    }

    /**
     * Menu diarahkan lewat rute yang valid; rute asing diabaikan.
     */
    public function test_rute_tidak_dikenal_diabaikan(): void
    {
        MenuItem::query()->create([
            'key' => 'tidak-ada',
            'label' => 'Rute Hilang',
            'route_name' => 'rute.tidak.ada',
            'pattern' => 'tidak-ada',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertSame([], MenuItem::navigation());
    }

    /**
     * Kolom Urutan pada panel admin kini menentukan susunan header: urutan di
     * dalam kelompok dan posisi kelompoknya. Sebelumnya susunan dikunci oleh
     * daftar kunci pada MenuItem::GROUPS sehingga Urutan tidak berpengaruh.
     */
    public function test_urutan_menu_dari_panel_menentukan_susunan_header(): void
    {
        $this->seed(MenuItemSeeder::class);

        // Galeri (8) diberi nomor 1 agar mendahului Berita (7).
        MenuItem::query()->where('key', 'gallery')->update(['sort_order' => 1]);
        MenuItem::flushNavigationCache();

        $menu = MenuItem::navigation();

        $publikasi = collect($menu)->firstWhere('label', 'Publikasi');

        $this->assertSame(['Galeri', 'Berita'], array_column($publikasi['items'], 'label'));

        // Kelompok Publikasi ikut naik karena memuat menu bernomor terkecil.
        $this->assertSame(['Beranda', 'Publikasi'], array_column(array_slice($menu, 0, 2), 'label'));

        $this->assertSame(
            ['Beranda', 'Publikasi', 'Galeri', 'Berita', 'Profil', 'Tentang', 'Wilayah', 'Program & Kegiatan', 'Program', 'Kegiatan', 'Cerita', 'Kontak'],
            $this->tautanNavigasi($this->get('/')->assertOk()->getContent()),
        );
    }

    /**
     * Menu tambahan dari panel diletakkan menurut Urutan, bukan selalu di akhir.
     */
    public function test_menu_tambahan_mengikuti_urutan(): void
    {
        $this->seed(MenuItemSeeder::class);

        MenuItem::query()->create([
            'key' => 'panduan',
            'label' => 'Panduan Relawan',
            'route_name' => 'contact',
            'pattern' => 'kontak',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        MenuItem::flushNavigationCache();

        $this->assertSame('Panduan Relawan', MenuItem::navigation()[0]['label']);
    }

    /**
     * Teks tautan navigasi header sesuai urutan render (termasuk tombol
     * pembuka kelompok tarik-turun).
     *
     * @return list<string>
     */
    private function tautanNavigasi(string $html): array
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $hasil = [];

        $nodes = $xpath->query('//nav//*[self::a or self::button][contains(@class, "site-nav-link") or contains(@class, "site-nav-dropdown-link")]');

        foreach ($nodes as $node) {
            $hasil[] = trim((string) preg_replace('/\s+/u', ' ', $node->textContent));
        }

        return $hasil;
    }
}
