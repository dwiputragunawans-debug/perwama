<?php

namespace Tests\Feature;

use App\Filament\Resources\PanelSettings\PanelSettingResource;
use App\Filament\Resources\WebsiteSettings\WebsiteSettingResource;
use App\Models\MenuItem;
use App\Models\PanelSetting;
use App\Models\User;
use App\Models\WebsiteSetting;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pengaturan panel di-cache statis; dibersihkan agar tiap uji berdiri sendiri.
        PanelSetting::flushCache();
        WebsiteSetting::flushCache();
    }

    /**
     * Setelah menyimpan, halaman "Buat"/"Simpan" kembali ke daftar record.
     */
    public function test_create_dan_edit_diarahkan_ke_daftar(): void
    {
        $panel = Filament::getCurrentOrDefaultPanel();

        $this->assertSame('index', $panel->getResourceCreatePageRedirect());
        $this->assertSame('index', $panel->getResourceEditPageRedirect());
    }

    /**
     * Gaya sidebar berlatar putih ikut tersuntik pada seluruh halaman panel.
     */
    public function test_sidebar_diberi_latar_putih(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('fi-body .fi-sidebar', false)
            ->assertSee('background-color: #ffffff', false);
    }

    /**
     * Tombol utama memakai warna brand #1F2A44 dengan teks putih.
     */
    public function test_tombol_utama_memakai_warna_brand_dan_teks_putih(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('--bg: #1F2A44', false)
            ->assertSee('--text: #ffffff', false);
    }

    /**
     * Ukuran huruf seluruh panel dinaikkan satu tingkat dari skala bawaan
     * Filament agar nyaman dibaca pengelola yang usianya sudah berumur:
     * text-xs 12->14px, text-sm 14->16px, text-base 16->18px, dst. Skala
     * disuntikkan lewat variabel `--text-*` pada setiap halaman panel.
     */
    public function test_ukuran_huruf_panel_dinaikkan_satu_tingkat(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('--text-xs: 0.875rem', false)
            ->assertSee('--text-xs--line-height: calc(1.25 / 0.875)', false)
            ->assertSee('--text-sm: 1rem', false)
            ->assertSee('--text-sm--line-height: calc(1.5 / 1)', false)
            ->assertSee('--text-base: 1.125rem', false)
            ->assertSee('--text-lg: 1.25rem', false)
            ->assertSee('--text-xl: 1.5rem', false)
            ->assertSee('--text-2xl: 1.875rem', false)
            ->assertSee('--text-3xl: 2.25rem', false)
            // Nama & slogan brand di samping logo (kelas milik proyek ini) ikut naik.
            ->assertSee('font-size: 1.125rem', false)
            ->assertSee('font-size: 0.875rem', false)
            // Teks yang mengikuti ukuran dasar ikut naik (body 18px).
            ->assertSee('font-size: var(--text-base)', false);

        // Skala yang sama berlaku pada halaman di dalam panel, bukan hanya login.
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('--text-xs: 0.875rem', false)
            ->assertSee('--text-sm: 1rem', false);
    }

    /**
     * Background halaman login mengikuti pengaturan tersendiri.
     */
    public function test_background_login_mengikuti_pengaturan(): void
    {
        PanelSetting::current()->update([
            'login_background' => 'uploads/panel/background-uji.jpg',
            'login_background_color' => '#101828',
        ]);
        PanelSetting::flushCache();

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('storage/uploads/panel/background-uji.jpg', false)
            ->assertSee('background-color: #101828', false);
    }

    /**
     * Logo panel dibaca dari pengaturan dan dipakai oleh panel.
     */
    public function test_logo_panel_dibaca_dari_pengaturan(): void
    {
        $setting = PanelSetting::current();
        $setting->update([
            'brand_logo' => 'uploads/panel/logo-uji.png',
            'brand_logo_height' => '3rem',
        ]);
        PanelSetting::flushCache();

        $panel = Filament::getCurrentOrDefaultPanel();

        $this->assertStringContainsString('uploads/panel/logo-uji.png', (string) $panel->getBrandLogo());
        $this->assertSame('3rem', $panel->getBrandLogoHeight());
    }

    /**
     * Logo kosong membuat panel kembali menampilkan nama brand.
     */
    public function test_logo_kosong_kembali_ke_nama_brand(): void
    {
        $this->assertNull(Filament::getCurrentOrDefaultPanel()->getBrandLogo());
    }

    /**
     * Favicon panel dibaca dari pengaturan yang sama dengan logo.
     */
    public function test_favicon_mengikuti_pengaturan(): void
    {
        PanelSetting::current()->update(['favicon' => 'uploads/panel/favicon-uji.png']);
        PanelSetting::flushCache();

        $this->assertStringContainsString(
            'uploads/panel/favicon-uji.png',
            (string) Filament::getCurrentOrDefaultPanel()->getFavicon(),
        );
    }

    /**
     * Topbar panel menampilkan nama dan slogan brand yang dapat disesuaikan.
     */
    public function test_topbar_panel_menampilkan_nama_dan_slogan(): void
    {
        PanelSetting::current()->update([
            'brand_name' => 'NAMA BRAND UJI',
            'brand_tagline' => 'Slogan brand uji',
        ]);
        PanelSetting::flushCache();

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('NAMA BRAND UJI', false)
            ->assertSee('Slogan brand uji', false);
    }

    /**
     * Logo diperbesar pada halaman login dan di dalam panel.
     */
    public function test_logo_diperbesar_pada_login_dan_panel(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('.fi-simple-header .fi-logo', false)
            ->assertSee('calc(2.5rem * 1.8)', false)
            ->assertSee('.fi-topbar-start .fi-logo', false);
    }

    /**
     * Halaman pengaturan menu dan tampilan panel dapat dibuka oleh admin.
     */
    public function test_halaman_pengaturan_dapat_dibuka_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/menu-items')->assertOk();
        $this->actingAs($admin)->get('/admin/menu-items/create')->assertOk();

        $this->actingAs($admin)->get('/admin/panel-settings')->assertOk();
        $this->actingAs($admin)->get('/admin/panel-settings/1/edit')->assertOk();

        $this->actingAs($admin)->get('/admin/website-settings')->assertOk();
        $this->actingAs($admin)->get('/admin/website-settings/1/edit')->assertOk();
    }

    /**
     * Form Tampilan Website menyediakan teks bagian "Wilayah" (judul dan
     * deskripsi) agar dapat disunting dari panel admin.
     */
    public function test_form_tampilan_website_menyediakan_teks_bagian_wilayah(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/website-settings/1/edit')
            ->assertOk()
            ->assertSee('Wilayah', false)
            ->assertSee('region_section_title', false)
            ->assertSee('region_section_description', false);
    }

    /**
     * Foto akun dipakai sebagai avatar panel; tanpa foto, panel memakai inisial.
     */
    public function test_avatar_panel_memakai_foto_akun(): void
    {
        $denganFoto = User::factory()->create(['photo' => 'uploads/users/foto-uji.png']);

        $this->assertStringContainsString('uploads/users/foto-uji.png', (string) $denganFoto->getFilamentAvatarUrl());

        $tanpaFoto = User::factory()->create(['photo' => null]);

        $this->assertNull($tanpaFoto->getFilamentAvatarUrl());
    }

    /**
     * Pengaturan tampilan bersifat tunggal (tidak dapat ditambah record baru).
     */
    public function test_pengaturan_tampilan_bersifat_tunggal(): void
    {
        $this->assertFalse(PanelSettingResource::canCreate());
        $this->assertFalse(WebsiteSettingResource::canCreate());

        PanelSetting::current();
        PanelSetting::current();
        WebsiteSetting::current();
        WebsiteSetting::current();

        $this->assertSame(1, PanelSetting::query()->count());
        $this->assertSame(1, WebsiteSetting::query()->count());
    }

    /**
     * Grup sidebar panel mengikuti menu website: sembilan grup pertama sama
     * persis (dan seurutan) dengan menu navigasi situs, lalu ditutup dua grup
     * internal panel ("Bergabung" untuk formulir publik dan "Pengaturan").
     */
    public function test_grup_sidebar_mengikuti_menu_website(): void
    {
        $grup = array_values(Filament::getCurrentOrDefaultPanel()->getNavigationGroups());

        $menuWebsite = array_map(
            fn (array $item): string => $item['label'],
            MenuItem::DEFAULTS,
        );

        $this->assertSame($menuWebsite, array_slice($grup, 0, count($menuWebsite)));
        $this->assertSame(['Bergabung', 'Pengaturan'], array_slice($grup, count($menuWebsite)));
    }

    /**
     * Tidak ada resource yang memakai grup di luar daftar menu website,
     * sehingga sidebar tidak lagi memecah satu halaman ke beberapa menu.
     */
    public function test_setiap_resource_memakai_grup_sidebar_yang_mengikuti_menu_website(): void
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $grup = array_values($panel->getNavigationGroups());

        $this->assertNotEmpty($grup);

        foreach ($panel->getResources() as $resource) {
            $this->assertContains(
                $resource::getNavigationGroup(),
                $grup,
                "Resource [{$resource}] memakai grup di luar daftar menu website.",
            );
        }
    }

    /**
     * Seluruh konten halaman "Tentang" pada website berkumpul dalam satu grup
     * sidebar, bukan tersebar di beberapa menu admin.
     */
    public function test_konten_halaman_tentang_berkumpul_dalam_satu_grup(): void
    {
        $panel = Filament::getCurrentOrDefaultPanel();

        $label = array_map(
            fn (string $resource): string => $resource::getNavigationLabel(),
            array_values(array_filter(
                $panel->getResources(),
                fn (string $resource): bool => $resource::getNavigationGroup() === 'Tentang',
            )),
        );

        sort($label);

        $this->assertSame(
            ['Identitas Organisasi', 'Mitra & Jejaring', 'Pengurus', 'Profil Organisasi', 'Sejarah', 'Statistik'],
            $label,
        );
    }

    /**
     * Sidebar yang benar-benar dirender menampilkan urutan grup menu website,
     * dan nama grup lama sudah tidak dipakai lagi.
     */
    public function test_sidebar_panel_menampilkan_grup_menu_website(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

        preg_match_all('/<span class="fi-sidebar-group-label">\s*([^<]+?)\s*<\/span>/', $html, $cocok);

        $this->assertSame([
            'Beranda',
            'Tentang',
            'Program',
            'Wilayah',
            'Kegiatan',
            'Cerita',
            'Berita',
            'Galeri',
            'Kontak',
            'Bergabung',
            'Pengaturan',
        ], $cocok[1]);

        $this->assertStringNotContainsString('Jaringan Wilayah', $html);
        $this->assertStringNotContainsString('Interaksi Publik', $html);
        $this->assertStringNotContainsString('Galeri &amp; Media', $html);
    }
}
