<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\News;
use App\Models\OrganizationProfile;
use App\Models\PanelSetting;
use App\Models\Program;
use App\Models\Publication;
use App\Models\Region;
use App\Models\Story;
use App\Models\Video;
use App\Models\WebsiteSetting;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Throwable;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pengaturan memakai cache statis; dibersihkan agar tiap uji berdiri sendiri.
        PanelSetting::flushCache();
        WebsiteSetting::flushCache();
        OrganizationProfile::flushCache();

        $this->seed();
    }

    /**
     * Setiap halaman publik harus dapat dirender tanpa error.
     */
    public function test_semua_halaman_publik_dapat_dirender(): void
    {
        $gagal = [];

        foreach ($this->publicUrls() as $url) {
            try {
                $response = $this->get($url);
            } catch (Throwable $e) {
                $gagal[] = $url.' → '.$e->getMessage();

                continue;
            }

            if ($response->getStatusCode() !== 200) {
                $gagal[] = $url.' → HTTP '.$response->getStatusCode();
            }
        }

        $this->assertSame([], $gagal, "Halaman publik berikut gagal dirender:\n".implode("\n", $gagal));
    }

    /**
     * Beranda menampilkan konten yang berasal dari seeder, tanpa bagian mitra.
     */
    public function test_beranda_menampilkan_konten_dari_seeder(): void
    {
        $berita = News::query()->orderByDesc('published_at')->firstOrFail();
        $berita->update(['title' => 'Kabar Unggulan Uji Coba']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Kabar Unggulan Uji Coba')
            ->assertDontSee('Bermitra bersama');
    }

    /**
     * Bagian "Berita" pada beranda memakai tata letak dua kolom: satu berita
     * unggulan berfoto besar (kategori, tanggal, judul, ringkasan, dan tautan
     * baca) di kolom kiri, serta tiga kartu ringkas berfoto persegi di kolom
     * kanan. Daftar teks tanpa gambar yang lama sudah tidak dipakai lagi.
     */
    public function test_bagian_berita_beranda_memakai_berita_unggulan_dan_tiga_kartu_ringkas(): void
    {
        $berita = News::query()->published()->ordered()->take(4)->get();

        $this->assertCount(4, $berita, 'Seeder berita beranda harus mengisi satu berita unggulan dan tiga kartu ringkas.');

        $response = $this->get('/')->assertOk();

        $response
            ->assertSee('Kabar terbaru')
            ->assertSee('Semua berita')
            ->assertSee('Baca berita')
            ->assertSee($berita[0]->title)
            ->assertSee($berita[1]->title)
            ->assertSee($berita[2]->title)
            ->assertSee($berita[3]->title);

        $html = $response->getContent();

        // Kategori berwarna terracotta, tanggal tetap memakai gaya meta.
        $this->assertSame(4, substr_count($html, 'class="card-kicker text-terracotta"'));

        // Berita unggulan memakai judul yang lebih besar pada layar lebar.
        $this->assertSame(1, substr_count($html, 'card-title-wide mt-4 font-display text-2xl leading-snug text-navy md:text-3xl'));

        // Tiga kartu ringkas memakai bidang kartu beranda dengan foto persegi.
        $this->assertSame(3, substr_count($html, 'class="card group flex gap-5 p-5"'));
        $this->assertSame(3, substr_count($html, 'card-media-square'));

        // Tata letak lama (daftar teks dengan garis pemisah) sudah tidak dipakai.
        $this->assertStringNotContainsString('lg:border-l lg:border-navy/10 lg:pl-14', $html);

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $bagian = null;

        foreach ($xpath->query('//main//section') as $kandidat) {
            if (str_contains($kandidat->textContent, 'Kabar terbaru')) {
                $bagian = $kandidat;
                break;
            }
        }

        $this->assertNotNull($bagian, 'Bagian "Berita" pada beranda tidak ditemukan.');

        $artikel = $xpath->query('.//article', $bagian);

        $this->assertSame(
            4,
            $artikel->length,
            'Bagian berita beranda harus memuat satu berita unggulan dan tiga kartu ringkas.',
        );

        // Tiga artikel terakhir adalah kartu ringkas: memakai bidang kartu
        // beranda (.card) dan memiliki thumbnail foto persegi.
        foreach ([1, 2, 3] as $urutan) {
            $kartu = $artikel->item($urutan);

            $this->assertNotNull($kartu);

            $this->assertStringContainsString('card ', $kartu->getAttribute('class').' ');

            $this->assertSame(
                1,
                $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " card-media-square ")]', $kartu)->length,
                'Setiap kartu berita ringkas harus memuat tepat satu foto persegi.',
            );
        }
    }

    /**
     * Gambar hero memakai unggahan khusus bila tersedia.
     */
    public function test_hero_memakai_gambar_unggahan_khusus(): void
    {
        WebsiteSetting::current()->update([
            'hero_images' => ['uploads/hero/satu.jpg', 'uploads/hero/dua.jpg'],
        ]);
        WebsiteSetting::flushCache();

        $this->get('/')
            ->assertOk()
            ->assertSee('uploads/hero/satu.jpg', false)
            ->assertSee('uploads/hero/dua.jpg', false);
    }

    /**
     * Judul dan deskripsi bagian "Wilayah" pada beranda mengikuti
     * Pengaturan → Tampilan Website sehingga dapat disesuaikan pengelola.
     */
    public function test_judul_dan_deskripsi_bagian_wilayah_mengikuti_pengaturan(): void
    {
        WebsiteSetting::current()->update([
            'region_section_title' => 'Jejaring Uji Coba Nusantara',
            'region_section_description' => 'Deskripsi sebaran wilayah hasil pengaturan pengelola.',
        ]);
        WebsiteSetting::flushCache();

        $this->get('/')
            ->assertOk()
            ->assertSee('Jejaring Uji Coba Nusantara')
            ->assertSee('Deskripsi sebaran wilayah hasil pengaturan pengelola.');
    }

    /**
     * Bila judul/deskripsi bagian "Wilayah" dikosongkan, beranda kembali
     * memakai teks bawaan agar bagian tersebut tidak pernah tampil kosong.
     */
    public function test_judul_dan_deskripsi_bagian_wilayah_memakai_teks_bawaan_saat_kosong(): void
    {
        WebsiteSetting::current()->update([
            'region_section_title' => null,
            'region_section_description' => null,
        ]);
        WebsiteSetting::flushCache();

        $this->get('/')
            ->assertOk()
            ->assertSee('Jejaring perempuan Maluku di seluruh Nusantara')
            ->assertSee('Dari Maluku hingga perantauan, PERWAMA INA hadir melalui wilayah, cabang, dan komunitas anggota yang tersebar di berbagai provinsi.');
    }

    /**
     * Bagian wilayah memakai istilah "Wilayah" (bukan "Di Indonesia") pada
     * judul halaman, navigasi, breadcrumb, dan footer.
     */
    public function test_bagian_wilayah_memakai_istilah_wilayah(): void
    {
        $this->get('/wilayah')
            ->assertOk()
            ->assertSee('Wilayah', false)
            ->assertDontSee('Di Indonesia', false);

        $wilayah = Region::query()->where('type', 'province')->firstOrFail();

        $this->get(route('region.show', $wilayah->slug))
            ->assertOk()
            ->assertSee('Wilayah', false)
            ->assertDontSee('Di Indonesia', false);
    }

    /**
     * Jenis wilayah tampil dalam bahasa Indonesia ("Provinsi"), bukan
     * "Province", pada halaman detail wilayah.
     */
    public function test_jenis_wilayah_memakai_bahasa_indonesia(): void
    {
        $wilayah = Region::query()->where('type', 'province')->firstOrFail();

        $this->get(route('region.show', $wilayah->slug))
            ->assertOk()
            ->assertSee('Provinsi', false)
            ->assertDontSee('Province', false);
    }

    /**
     * Halaman Tentang Kami tidak lagi menampilkan bagian "Mitra". CTA "Ingin
     * tahu lebih banyak?" kini diikuti bagian Identitas Organisasi, bukan
     * langsung footer.
     *
     * Jeda atas CTA = padding bawah `.section-y` bagian sebelumnya (5rem di
     * ponsel, 6.25rem di layar besar) + `pt-20` / `md:pt-25` yang nilainya sama
     * dengan `.section-y`, yaitu 10rem / 12.5rem. Padding bawah CTA memakai satu
     * langkah `.section-y` (`pb-20` / `md:pb-25`); bagian Identitas Organisasi
     * menambahkan padding atasnya sendiri sehingga jarak antarbagian tetap
     * 10rem / 12.5rem tanpa celah berlebih.
     */
    public function test_halaman_tentang_tanpa_mitra_dan_cta_diikuti_identitas_organisasi(): void
    {
        $response = $this->get('/tentang')->assertOk();

        $html = $response->getContent();

        // Bagian mitra beserta judulnya sudah hilang dari halaman.
        $this->assertStringNotContainsString('Mitra &amp; Kolaborator', $html);
        $this->assertStringNotContainsString('section-eyebrow">Mitra<', $html);

        $response->assertSee('Ingin tahu lebih banyak?');

        $cta = $this->bagianCta($html);

        $this->assertNotNull($cta, 'Bagian penutup "Ingin tahu lebih banyak?" tidak ditemukan.');

        // Padding .section-y tetangga di atas kartu penutup menentukan jeda
        // atas; nilainya dibaca dari CSS supaya perubahan `.section-y` ikut
        // menyeret ritme penutup halaman.
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression(
            '/\.section-y\s*\{[^}]*padding-block:\s*([\d.]+)rem;/s',
            $css,
            'Padding .section-y di ponsel tidak ditemukan di resources/css/app.css.',
        );
        $this->assertMatchesRegularExpression(
            '/@media \(min-width: 768px\)\s*\{\s*\.section-y\s*\{[^}]*padding-block:\s*([\d.]+)rem;/s',
            $css,
            'Padding .section-y di layar besar tidak ditemukan di resources/css/app.css.',
        );

        preg_match('/\.section-y\s*\{[^}]*padding-block:\s*([\d.]+)rem;/s', $css, $ponsel);
        preg_match('/@media \(min-width: 768px\)\s*\{\s*\.section-y\s*\{[^}]*padding-block:\s*([\d.]+)rem;/s', $css, $layarBesar);

        // 1rem = 4 satuan skala spasi Tailwind: padding atas dan bawah CTA
        // masing-masing satu langkah .section-y. Bagian berikutnya menambahkan
        // padding atasnya sendiri sehingga ritme antarbagian tetap utuh.
        $diharapkan = [
            'pt-'.(float) $ponsel[1] * 4,
            'pb-'.(float) $ponsel[1] * 4,
            'md:pt-'.(float) $layarBesar[1] * 4,
            'md:pb-'.(float) $layarBesar[1] * 4,
        ];

        $kelas = array_values(array_filter(preg_split('/\s+/', trim($cta->getAttribute('class'))) ?: []));

        $paddingBagian = array_values(array_filter(
            $kelas,
            fn (string $token): bool => (bool) preg_match('/^((sm|md|lg|xl|2xl):)?p[btxy]-/', $token),
        ));

        $this->assertEqualsCanonicalizing(
            $diharapkan,
            $paddingBagian,
            'CTA halaman Tentang harus memakai '.implode(' ', $diharapkan)
                .' agar jaraknya ke bagian di atas dan bagian Identitas Organisasi '
                .'di bawahnya tetap mengikuti ritme antar bagian halaman.',
        );
    }

    /**
     * Pembungkus navigasi halaman tidak dirender bila daftar hanya satu halaman.
     *
     * Regresi: div kosong ber-margin (`mt-12` = 3rem / `mt-14` = 3.5rem) tetap
     * menyumbang ruang di dalam bagian terakhir halaman daftar — mis. /berita
     * menjadi 128px bukan 80px, dan pada halaman Cerita jeda di atas kartu
     * penutup menjadi 208px/248px sedangkan di bawahnya 160px/200px.
     */
    public function test_daftar_satu_halaman_tidak_menyisakan_pembungkus_paginasi_kosong(): void
    {
        foreach ($this->publicUrls() as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertDoesNotMatchRegularExpression(
                '/<div class="mt-1[24]">\s*<\/div>/',
                $html,
                "Ada pembungkus navigasi halaman yang kosong pada {$url}; div ber-margin tetap menambah jarak di dalam bagian terakhir halaman.",
            );
        }
    }

    /**
     * Navigasi halaman tetap dirender saat daftar memuat lebih dari satu halaman.
     *
     * Penjaga `hasPages()` pada halaman daftar hanya membuang pembungkus kosong:
     * begitu jumlah item melewati batas per halaman, div ber-margin dan tautan
     * ke halaman kedua harus tetap ada.
     */
    public function test_paginasi_tetap_tampil_saat_daftar_lebih_dari_satu_halaman(): void
    {
        // 8 berita terbit bawaan + 4 salinan = 12 item, melewati 9 per halaman.
        News::query()->published()->ordered()->limit(4)->get()
            ->each(function (News $news, int $index): void {
                $salinan = $news->replicate();
                $salinan->title = $news->title.' — salinan '.($index + 1);
                $salinan->slug = $news->slug.'-salinan-'.($index + 1);
                $salinan->save();
            });

        $html = $this->get('/berita')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<div class="mt-12">\s*<nav\b/',
            $html,
            'Pembungkus navigasi halaman hilang padahal daftar berita memuat lebih dari satu halaman.',
        );
        $this->assertStringContainsString(
            'page=2',
            $html,
            'Tautan ke halaman kedua tidak dirender.',
        );
    }

    /**
     * Elemen <section> pembungkus bagian CTA berdasarkan judulnya.
     */
    private function bagianCta(string $html): ?\DOMElement
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        foreach ((new DOMXPath($dom))->query('//main//section') as $section) {
            if (str_contains($section->textContent, 'Ingin tahu lebih banyak?')) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Bagian "Tentang kami" pada beranda menampilkan profil, visi, dan misi
     * sebagai tiga kartu sejajar: judul bagian terpusat, judul kartu terpusat,
     * deskripsi dengan batas panjang yang sama, dan tombol CTA kuning (gold)
     * "Lihat Selengkapnya" yang mengarah ke halaman Tentang Kami. Warna tombol
     * kartu sama dengan tombol emas utama situs pada hero.
     */
    public function test_beranda_memakai_tiga_kartu_tentang_visi_misi(): void
    {
        $profil = OrganizationProfile::current();

        $response = $this->get('/')->assertOk();

        $response
            ->assertSee('Tentang kami')
            ->assertSee($profil->name)
            ->assertSee($profil->vision)
            ->assertSee(mb_substr(strip_tags($profil->description), 0, 40), false)
            ->assertSee(mb_substr($profil->mission[0], 0, 40), false)
            ->assertSee('btn btn-gold', false);

        $html = $response->getContent();

        // Tiga kartu sejajar, masing-masing memiliki satu tombol CTA.
        $this->assertSame(3, substr_count($html, 'class="card flex h-full flex-col text-center"'));

        // Deskripsi tiap kartu memakai batas teks yang sama agar seragam.
        $this->assertSame(3, substr_count($html, 'card-text-clamp'));

        // Tombol ketiga kartu berbunyi "Lihat Selengkapnya" ke halaman Tentang
        // Kami dan memakai warna kuning (gold), sama dengan tombol lain.
        $this->assertSame(3, substr_count($html, 'Lihat Selengkapnya'));
        $this->assertSame(3, substr_count($html, 'href="'.route('about').'" class="btn btn-gold"'));
        $this->assertStringNotContainsString('btn-terracotta', $html);

        // Warna tombol kartu disamakan dengan tombol emas acuan situs (tombol
        // hero "Bergabung Bersama Kami"): keduanya memakai kelas "btn btn-gold"
        // yang sama, sehingga warnanya selalu identik dengan tombol utama.
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $tombolAcuan = $xpath->query(
            '//main//a[contains(concat(" ", normalize-space(@class), " "), " btn-gold ") and contains(normalize-space(.), "Bergabung Bersama Kami")]'
        )->item(0);

        $this->assertNotNull($tombolAcuan, 'Tombol emas acuan pada hero beranda tidak ditemukan.');
        $this->assertSame('btn btn-gold', trim($tombolAcuan->getAttribute('class')));

        $tombolKartu = $xpath->query('//main//a[contains(normalize-space(.), "Lihat Selengkapnya")]');

        $this->assertSame(3, $tombolKartu->length);

        foreach ($tombolKartu as $tombol) {
            $this->assertSame(
                trim($tombolAcuan->getAttribute('class')),
                trim($tombol->getAttribute('class')),
                'Tombol "Lihat Selengkapnya" harus memakai warna gold yang sama dengan tombol emas utama situs.',
            );
        }

        $this->assertStringNotContainsString('Lihat program', $html);
        $this->assertStringNotContainsString('Selengkapnya tentang kami', $html);
    }

    /**
     * Gambar ketiga kartu "Tentang kami" pada beranda mengikuti unggahan
     * pengelola pada Profil Organisasi (satu tempat dengan isian visi & misi).
     */
    public function test_gambar_kartu_beranda_mengikuti_unggahan_pengelola(): void
    {
        OrganizationProfile::current()->update([
            'about_card_image' => 'uploads/about-cards/tentang.jpg',
            'vision_card_image' => 'uploads/about-cards/visi.jpg',
            'mission_card_image' => 'uploads/about-cards/misi.jpg',
        ]);
        OrganizationProfile::flushCache();

        $this->get('/')
            ->assertOk()
            ->assertSee('uploads/about-cards/tentang.jpg', false)
            ->assertSee('uploads/about-cards/visi.jpg', false)
            ->assertSee('uploads/about-cards/misi.jpg', false);
    }

    /**
     * Bila gambar kartu belum diunggah, ketiga kartu kembali memakai bidang
     * bermerek (placeholder) sehingga tampilan tetap rapi.
     */
    public function test_gambar_kartu_beranda_memakai_placeholder_bila_kosong(): void
    {
        OrganizationProfile::current()->update([
            'about_card_image' => null,
            'vision_card_image' => null,
            'mission_card_image' => null,
        ]);
        OrganizationProfile::flushCache();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, 'media-placeholder'));
        $this->assertStringNotContainsString('uploads/about-cards', $html);
    }

    /**
     * Deskripsi kartu beranda dibatasi panjangnya sehingga bentuk ketiga
     * kartu tetap seragam walau teks dari panel admin jauh lebih panjang.
     */
    public function test_deskripsi_kartu_beranda_dibatasi_panjang_teksnya(): void
    {
        $panjang = str_repeat('Visi dan misi organisasi yang sengaja dibuat sangat panjang untuk menguji pembatasan teks pada kartu beranda. ', 3);

        OrganizationProfile::current()->update([
            'description' => '<p>'.$panjang.'</p>',
            'vision' => $panjang,
            'mission' => [$panjang],
        ]);
        OrganizationProfile::flushCache();

        $html = $this->get('/')->assertOk()->getContent();

        // Teks penuh tidak pernah tampil; yang tampil adalah versi terpotong.
        $this->assertStringNotContainsString(trim($panjang), $html);
        $this->assertStringContainsString(Str::limit(trim($panjang), 200), $html);
    }

    /**
     * Header website memakai sumber logo yang sama dengan panel admin.
     */
    public function test_header_website_memakai_logo_panel(): void
    {
        PanelSetting::current()->update(['brand_logo' => 'uploads/panel/logo-header.png']);
        PanelSetting::flushCache();

        $this->get('/')
            ->assertOk()
            ->assertSee('uploads/panel/logo-header.png', false);
    }

    /**
     * Identitas organisasi tampil dua baris pada header — nama singkat di atas
     * dan nama lengkap di bawahnya — pada semua ukuran layar, termasuk ponsel.
     *
     * Regresi: baris nama lengkap dulu memakai `hidden sm:block` sehingga di
     * layar di bawah 640px hanya singkatan "PERWAMA INA" yang terlihat.
     */
    public function test_header_menampilkan_nama_lengkap_di_bawah_singkatan(): void
    {
        $panel = PanelSetting::current();
        $tagline = $panel->displayTagline();

        $this->assertNotEmpty($tagline, 'Nama lengkap organisasi harus tersedia sebagai baris kedua header.');

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get('/')->assertOk()->getContent());
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $header = $xpath->query('//*[@data-site-header]')->item(0);

        $this->assertNotNull($header, 'Header situs [data-site-header] tidak ditemukan pada beranda.');

        // Nama singkat dan nama lengkap berada pada satu tautan merek yang sama.
        $merek = $xpath->query('.//a[@href="'.route('home').'"][contains(@class, "items-center")]', $header)->item(0);

        $this->assertNotNull($merek, 'Tautan merek (logo + nama) tidak ditemukan pada header.');
        $this->assertStringContainsString($panel->displayName(), $merek->textContent);
        $this->assertStringContainsString($tagline, $merek->textContent);

        // Baris nama lengkap harus tampil tanpa syarat lebar layar.
        foreach ($xpath->query('.//span', $merek) as $span) {
            if (trim($span->textContent) !== $tagline || $span->getElementsByTagName('span')->length) {
                continue;
            }

            $kelas = $span->getAttribute('class');

            $this->assertStringContainsString('block', $kelas, 'Baris nama lengkap harus tampil sebagai baris tersendiri.');
            $this->assertStringNotContainsString('hidden', $kelas, 'Nama lengkap organisasi disembunyikan pada layar kecil.');
            $this->assertStringNotContainsString('sm:block', $kelas, 'Nama lengkap organisasi hanya muncul mulai 640px.');

            return;
        }

        $this->fail('Baris nama lengkap organisasi tidak ditemukan di bawah nama singkat pada header.');
    }

    /**
     * Bila slogan panel dikosongkan, baris kedua header website mengikuti
     * kolom "Nama" pada Profil Organisasi — sehingga mengubah nama organisasi
     * lewat admin langsung terlihat pada header.
     *
     * Regresi: slogan lama yang masih tersimpan di Pengaturan → Tampilan Panel
     * menimpa nama organisasi, sehingga perubahan nama tidak pernah tampil.
     */
    public function test_header_mengikuti_nama_profil_saat_slogan_panel_dikosongkan(): void
    {
        OrganizationProfile::current()->update(['name' => 'Persatuan Wanita Maluku Indonesia']);
        PanelSetting::current()->update(['brand_tagline' => null]);
        PanelSetting::flushCache();
        OrganizationProfile::flushCache();

        $this->assertStringContainsString(
            'Persatuan Wanita Maluku Indonesia',
            $this->teks_merek_header(),
            'Header harus memakai nama organisasi dari Profil Organisasi bila slogan panel kosong.',
        );
    }

    /**
     * Slogan pada Pengaturan → Tampilan Panel bersifat menimpa: bila diisi,
     * teks itulah yang tampil pada header, bukan nama organisasi.
     */
    public function test_slogan_panel_menimpa_nama_organisasi_pada_header(): void
    {
        OrganizationProfile::current()->update(['name' => 'Persatuan Wanita Maluku Indonesia']);
        PanelSetting::current()->update(['brand_tagline' => 'Slogan khusus panel']);
        PanelSetting::flushCache();
        OrganizationProfile::flushCache();

        $teks = $this->teks_merek_header();

        $this->assertStringContainsString('Slogan khusus panel', $teks);
        $this->assertStringNotContainsString('Persatuan Wanita Maluku Indonesia', $teks);
    }

    /**
     * Teks pada tautan merek (logo + nama + slogan) di header situs.
     */
    private function teks_merek_header(): string
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get('/')->assertOk()->getContent());
        libxml_clear_errors();

        $merek = (new DOMXPath($dom))
            ->query('//*[@data-site-header]//a[@href="'.route('home').'"]')
            ->item(0);

        $this->assertNotNull($merek, 'Tautan merek (logo + nama) tidak ditemukan pada header.');

        return $merek->textContent;
    }

    /**
     * Hero beranda memakai carousel foto sebagai latar penuh, dilengkapi
     * overlay gelap, kontrol navigasi, dan indikator carousel. Kartu agenda
     * tidak lagi ditampilkan karena belum dibutuhkan di beranda.
     */
    public function test_hero_beranda_memakai_carousel_latar_penuh(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('hero-full', false)
            ->assertSee('hero-overlay', false)
            ->assertSee('hero-bg-slide', false)
            ->assertSee('data-carousel-slide', false)
            ->assertSee('hero-bg-controls', false)
            ->assertSee('hero-bg-dots', false)
            ->assertDontSee('Agenda terdekat', false);
    }

    /**
     * Kontrol dan indikator carousel harus berada di dalam elemen
     * [data-carousel] agar ditemukan oleh carousel.querySelector() pada app.js.
     *
     * Regresi: sebelumnya keduanya diletakkan di luar wadah sehingga tombol
     * berikutnya/sebelumnya tidak berfungsi dan titik indikator tidak bergerak.
     */
    public function test_kontrol_dan_indikator_carousel_berada_di_dalam_wadah(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $carousel = $xpath->query('//*[@data-carousel]')->item(0);

        $this->assertNotNull($carousel, 'Elemen [data-carousel] tidak ditemukan pada beranda.');

        $this->assertSame(
            1,
            $xpath->query('.//*[@data-carousel-prev]', $carousel)->length,
            'Tombol "sebelumnya" harus berada di dalam [data-carousel].',
        );

        $this->assertSame(
            1,
            $xpath->query('.//*[@data-carousel-next]', $carousel)->length,
            'Tombol "berikutnya" harus berada di dalam [data-carousel].',
        );

        $this->assertGreaterThanOrEqual(
            2,
            $xpath->query('.//*[@data-carousel-dot]', $carousel)->length,
            'Titik indikator harus berada di dalam [data-carousel].',
        );
    }

    /**
     * Isi hero beranda memesan ruang bawah ekstra pada ponsel agar deretan
     * indikator carousel tidak menempel pada tombol CTA.
     *
     * Regresi: di ponsel tombol "Jelajahi Program" menumpuk selebar layar,
     * sedangkan isi hero hanya memakai padding bawah 3rem (py-12) sementara
     * indikator dipatok 2.5rem dari dasar hero — tersisa ~8px saja sehingga
     * titik indikator tampak menempel pada tombol. Kini ponsel memakai pb-20
     * (5rem) sehingga jeda ke titik indikator tersisa ±2.1rem, sementara layar
     * besar (md:py-16) tidak berubah.
     */
    public function test_hero_beranda_memesan_ruang_bawah_untuk_indikator(): void
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get('/')->assertOk()->getContent());
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $hero = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' hero-full ')]")->item(0);
        $this->assertNotNull($hero, 'Bagian hero beranda tidak ditemukan.');

        $konten = $xpath->query(
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' container-page ')]",
            $hero,
        )->item(0);
        $this->assertNotNull($konten, 'Wadah isi hero beranda tidak ditemukan.');

        $kelas = preg_split('/\s+/', trim($konten->getAttribute('class'))) ?: [];

        $this->assertContains(
            'pb-20',
            $kelas,
            'Isi hero harus memesan padding bawah 5rem di ponsel agar indikator tidak menempel pada tombol CTA.',
        );
        $this->assertNotContains(
            'py-12',
            $kelas,
            'Padding bawah 3rem pada ponsel membuat titik indikator nyaris bersentuhan dengan tombol "Jelajahi Program".',
        );
        $this->assertContains(
            'md:py-16',
            $kelas,
            'Padding layar besar harus tetap md:py-16 agar tata letak desktop tidak berubah.',
        );

        $this->assertNotNull(
            $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' hero-bg-dots ')]", $hero)->item(0),
            'Deretan indikator carousel tidak ditemukan di dalam hero.',
        );

        // Jarak indikator dari dasar hero dipatok di CSS; dipadukan dengan
        // padding bawah 5rem maka selalu tersisa 2.5rem jeda ke tombol CTA.
        $this->assertMatchesRegularExpression(
            '/\.hero-bg-dots\s*\{[^}]*bottom:\s*2\.5rem;/s',
            file_get_contents(resource_path('css/app.css')),
            'Jarak indikator dari dasar hero berubah; sesuaikan padding isi hero pada home.blade.php.',
        );
    }

    /**
     * Hero beranda mengisi satu layar penuh di bawah header sticky.
     *
     * Tinggi header 4.5rem saat bilah kontak belum tampil (ponsel/tablet) dan
     * 7.5rem pada layar >=1280px ketika bilah kontak muncul. Satuan `svh`
     * menjaga tinggi tetap stabil saat bilah alamat ponsel berubah, sedangkan
     * `vh` menjadi cadangan untuk peramban lama.
     */
    public function test_hero_beranda_mengisi_satu_layar_penuh(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression(
            '/\.hero-full\s*\{[^}]*min-height:\s*calc\(100svh - 4\.5rem\);/s',
            $css,
            'Hero beranda harus mengisi layar penuh di bawah header 4.5rem pada ponsel/tablet.',
        );

        $this->assertMatchesRegularExpression(
            '/@media \(min-width: 1280px\)\s*\{\s*\.hero-full\s*\{[^}]*min-height:\s*calc\(100svh - 7\.5rem\);/s',
            $css,
            'Hero beranda harus dikurangi tinggi header 7.5rem saat bilah kontak tampil.',
        );
    }

    /**
     * Teks hero beranda dipusatkan dan tidak lagi memuat kartu "Agenda
     * terdekat" di sisi kanan.
     */
    public function test_hero_beranda_menempatkan_teks_di_tengah_tanpa_kartu_agenda(): void
    {
        $response = $this->get('/')->assertOk()->assertDontSee('Agenda terdekat', false);

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $hero = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' hero-full ')]")->item(0);
        $this->assertNotNull($hero, 'Bagian hero beranda tidak ditemukan.');

        $wadah = $xpath->query(
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' container-page ')]",
            $hero,
        )->item(0);
        $this->assertNotNull($wadah, 'Wadah isi hero beranda tidak ditemukan.');

        $teks = $xpath->query(
            "./div[contains(concat(' ', normalize-space(@class), ' '), ' text-center ')]",
            $wadah,
        )->item(0);
        $this->assertNotNull($teks, 'Blok teks hero harus dibungkus wadah ber-text-center.');

        $kelas = preg_split('/\s+/', trim($teks->getAttribute('class'))) ?: [];

        $this->assertContains(
            'mx-auto',
            $kelas,
            'Blok teks hero harus dipusatkan secara horizontal dengan mx-auto.',
        );
        $this->assertContains(
            'max-w-4xl',
            $kelas,
            'Blok teks hero harus dibatasi agar tetap nyaman dibaca saat teks dipusatkan.',
        );

        $judul = $xpath->query('.//h1', $teks)->item(0);
        $this->assertNotNull($judul, 'Judul hero beranda tidak ditemukan.');

        $kelasJudul = preg_split('/\s+/', trim($judul->getAttribute('class'))) ?: [];
        $this->assertContains(
            'mx-auto',
            $kelasJudul,
            'Judul hero harus dipusatkan di dalam blok teks.',
        );
        $this->assertContains(
            'max-w-4xl',
            $kelasJudul,
            'Lebar judul hero harus dibatasi agar barisnya tetap seimbang.',
        );

        $aksi = $xpath->query(
            ".//div[contains(concat(' ', normalize-space(@class), ' '), ' flex-wrap ')]",
            $teks,
        )->item(0);
        $this->assertNotNull($aksi, 'Deretan tombol hero beranda tidak ditemukan.');

        $kelasAksi = preg_split('/\s+/', trim($aksi->getAttribute('class'))) ?: [];
        $this->assertContains(
            'justify-center',
            $kelasAksi,
            'Tombol hero harus ikut terpusat bersama judul dan deskripsi.',
        );
    }

    /**
     * Elemen berkelas font-display di dalam kartu berlatar navy harus memakai
     * warna teks eksplisit. Tanpa itu teks mewarisi warna navy dari aturan
     * dasar ".font-display" (base layer) sehingga gelap di atas gelap.
     *
     * Regresi: deskripsi Visi pada halaman Tentang sebelumnya tidak terbaca.
     */
    public function test_teks_font_display_pada_kartu_navy_memakai_warna_eksplisit(): void
    {
        $langgar = [];

        foreach ($this->publicUrls() as $url) {
            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML($this->get($url)->assertOk()->getContent());
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            $kartuNavy = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' bg-navy ')]");

            foreach ($kartuNavy as $kartu) {
                $teks = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' font-display ')]", $kartu);

                foreach ($teks as $node) {
                    $kelas = $node->getAttribute('class');

                    if (! preg_match('/(?:^|\s)text-(?:white|navy|gold|ink|terracotta|green|meta|cream|black)(?:\s|$|\/|-)/', $kelas)) {
                        $langgar[] = $url.' → '.trim(preg_replace('/\s+/', ' ', $node->textContent));
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $langgar,
            "Teks berkelas font-display berikut tampil tanpa warna eksplisit di kartu navy:\n".implode("\n", $langgar),
        );
    }

    /**
     * Footer tidak boleh menambah margin atas.
     *
     * Setiap halaman mengakhiri kontennya dengan <section> yang sudah memiliki
     * padding bawah (mis. .section-y = 80–100px atau pt-20 pb-40 / md:pt-25
     * md:pb-50 = 160–200px pada kartu penutup).
     * Menambahkan margin atas pada footer membuat dua jeda bertumpuk sehingga
     * jarak ke footer terasa terlalu jauh, terutama pada CTA "Bergabung" beranda.
     */
    public function test_footer_tidak_menambah_margin_atas_bertumpuk(): void
    {
        foreach ($this->publicUrls() as $url) {
            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML($this->get($url)->assertOk()->getContent());
            libxml_clear_errors();

            $footer = (new DOMXPath($dom))->query('//footer')->item(0);

            $this->assertNotNull($footer, "Elemen <footer> tidak ditemukan pada {$url}.");

            $marginAtas = array_values(array_filter(
                preg_split('/\s+/', trim($footer->getAttribute('class'))) ?: [],
                fn (string $token): bool => str_starts_with($token, 'mt-'),
            ));

            $this->assertSame(
                [],
                $marginAtas,
                "Footer pada {$url} memakai margin atas (".implode(', ', $marginAtas).') '
                    .'yang menumpuk dengan padding section terakhir.',
            );
        }
    }

    /**
     * Penyegar sesi mengembalikan token CSRF terbaru untuk formulir.
     */
    public function test_penyegar_sesi_mengembalikan_token_csrf(): void
    {
        $response = $this->get(route('session.refresh'));

        $response->assertOk()->assertJsonStructure(['token']);
        $this->assertSame(40, strlen((string) $response->json('token')));
    }

    /**
     * Halaman 419 memberi penjelasan yang ramah, bukan halaman buntu.
     */
    public function test_halaman_419_memberi_penjelasan(): void
    {
        $html = view('errors.419')->render();

        $this->assertStringContainsString('Sesi Anda sudah berakhir', $html);
        $this->assertStringContainsString(route('join'), $html);
    }

    /**
     * Pencarian global mengembalikan hasil dari konten yang di-seed.
     */
    public function test_pencarian_menemukan_konten(): void
    {
        $program = Program::query()->firstOrFail();

        $this->get('/cari?q='.urlencode($program->title))
            ->assertOk()
            ->assertSee($program->title);
    }

    /**
     * Formulir publik menyimpan data yang dikirim pengunjung.
     */
    public function test_formulir_publik_menyimpan_data(): void
    {
        $this->post('/kontak', [
            'name' => 'Maria Kainama',
            'email' => 'maria@example.com',
            'phone' => '081200000001',
            'subject' => 'Kerja sama pelatihan',
            'message' => 'Kami ingin mengajak PERWAMA INA menyelenggarakan pelatihan literasi digital.',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', ['email' => 'maria@example.com']);

        $this->post('/bergabung', [
            'full_name' => 'Selviana Tanamal',
            'whatsapp' => '081200000002',
            'email' => 'selviana@example.com',
            'membership_type' => 'anggota',
            'city' => 'Ambon',
            'province' => 'Maluku',
        ])->assertRedirect(route('join'));

        $this->assertDatabaseHas('membership_applications', ['email' => 'selviana@example.com']);

        $this->post('/bergabung/mitra', [
            'name' => 'Yohanes Pattiasina',
            'organization_name' => 'Komunitas Pesisir Ambon',
            'email' => 'yohanes@example.com',
            'type' => 'mitra',
            'message' => 'Kami tertarik bermitra pada program pesisir dan lingkungan.',
        ])->assertRedirect(route('join'));

        $this->assertDatabaseHas('partnership_requests', ['email' => 'yohanes@example.com']);
    }

    /**
     * Informasi kontak tidak boleh meluber melewati padding kanan halaman.
     *
     * Regresi: `<dd>` tanpa `min-w-0` membuat kolom kisi memakai lebar
     * min-content dari surel `sekretariat@perwama-ina.or.id` (189px pada 14px),
     * sehingga pada layar ±340px trek kisi lebih lebar daripada isi
     * `.container-page`: alamat, label, dan judul bagian terpotong di tepi
     * kanan. Label sekarang bertumpuk di atas nilainya pada layar sempit dan
     * setiap nilai boleh menyusut serta dipatahkan.
     */
    public function test_informasi_kontak_tidak_meluber_pada_layar_sempit(): void
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get('/kontak')->assertOk()->getContent());
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $baris = $xpath->query('//dl[.//dt[normalize-space()="Alamat"]]/div');

        $this->assertGreaterThan(0, $baris->length, 'Daftar informasi kontak tidak ditemukan pada /kontak.');

        foreach ($baris as $div) {
            $kelas = $div->getAttribute('class');

            $this->assertStringContainsString('flex-col', $kelas, 'Label dan nilai informasi kontak harus bertumpuk pada layar sempit.');
            $this->assertStringContainsString('sm:flex-row', $kelas, 'Label dan nilai informasi kontak harus kembali sejajar mulai 640px.');

            $nilai = $xpath->query('./dd', $div)->item(0);

            $this->assertNotNull($nilai, 'Setiap label informasi kontak harus memiliki nilai <dd>.');

            $kelasNilai = $nilai->getAttribute('class');

            $this->assertStringContainsString('min-w-0', $kelasNilai, 'Nilai tanpa `min-w-0` memaksa kolom kisi melebar ke kanan.');
            $this->assertStringContainsString('wrap-anywhere', $kelasNilai, 'Surel dan tautan panjang harus boleh dipatahkan.');
        }

        $kolom = $xpath->query('//dl[.//dt[normalize-space()="Alamat"]]/parent::div')->item(0);

        $this->assertNotNull($kolom, 'Kolom informasi kontak tidak ditemukan.');
        $this->assertStringContainsString('min-w-0', $kolom->getAttribute('class'), 'Kolom kisi informasi kontak harus boleh menyusut.');
    }

    /**
     * Nomor WhatsApp tanpa kode negara tetap menghasilkan tautan chat yang sah.
     *
     * Regresi: tautan lama hanya membuang karakter non-angka, sehingga nomor
     * "0896…" menjadi `wa.me/0896…` dan tidak dikenali WhatsApp. Nomor lokal
     * kini dinormalkan ke format `62…`; teks yang tampil tetap seperti yang
     * diisi pengelola.
     */
    public function test_tautan_whatsapp_menormalkan_nomor_lokal_tanpa_kode_negara(): void
    {
        $this->assertSame('https://wa.me/6289662135974', whatsapp_url('0896-6213-5974'));
        $this->assertSame('https://wa.me/6289662135974', whatsapp_url('89662135974'));
        $this->assertSame('https://wa.me/6289662135974', whatsapp_url('+62 896 6213 5974'));
        $this->assertSame('https://wa.me/6289662135974', whatsapp_url('62 896 6213 5974'));
        $this->assertSame('https://wa.me/6289662135974', whatsapp_url('0062 896 6213 5974'));
        $this->assertSame('https://wa.me/6289662135974', whatsapp_url('62 0896 6213 5974'));
        $this->assertNull(whatsapp_url(''));
        $this->assertNull(whatsapp_url(null));
    }

    /**
     * Tautan WhatsApp pada Tentang, Kontak, header, dan footer memakai nomor
     * yang sudah dinormalkan, bukan nomor lokal apa adanya.
     */
    public function test_halaman_tentang_dan_kontak_memakai_tautan_whatsapp_yang_benar(): void
    {
        OrganizationProfile::current()->update(['whatsapp' => '0896-6213-5974']);
        OrganizationProfile::flushCache();

        foreach (['/tentang', '/kontak'] as $url) {
            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML($this->get($url)->assertOk()->getContent());
            libxml_clear_errors();

            $tautan = (new DOMXPath($dom))->query('//a[starts-with(@href, "https://wa.me/")]');

            $this->assertGreaterThanOrEqual(
                3,
                $tautan->length,
                "Tautan WhatsApp pada header, isi, dan footer tidak lengkap di {$url}.",
            );

            foreach ($tautan as $anchor) {
                $this->assertSame(
                    'https://wa.me/6289662135974',
                    $anchor->getAttribute('href'),
                    "Tautan WhatsApp pada {$url} harus memakai kode negara 62.",
                );
            }

            $this->assertStringContainsString(
                '0896-6213-5974',
                $dom->textContent,
                "Nomor WhatsApp tetap ditampilkan seperti yang diisi pengelola pada {$url}.",
            );
        }
    }

    /**
     * Kartu sekretariat pada halaman Tentang Kami memakai aturan yang sama.
     *
     * Regresi: surel panjang pada `<dd>` membuat min-content kartu 373px,
     * sehingga seluruh kolom kiri halaman ikut melebar dan terpotong pada
     * layar 320–390px.
     */
    public function test_kartu_sekretariat_tentang_tidak_meluber_pada_layar_sempit(): void
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get('/tentang')->assertOk()->getContent());
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $baris = $xpath->query('//dl[.//dt[normalize-space()="Email"]]/div[.//a[starts-with(@href, "mailto:")]]');

        $this->assertGreaterThan(0, $baris->length, 'Baris surel sekretariat tidak ditemukan pada /tentang.');

        foreach ($baris as $div) {
            $kelas = $div->getAttribute('class');

            $this->assertStringContainsString('flex-col', $kelas, 'Label dan surel sekretariat harus bertumpuk pada layar sempit.');
            $this->assertStringContainsString('sm:flex-row', $kelas, 'Label dan surel sekretariat harus kembali sejajar mulai 640px.');

            $nilai = $xpath->query('./dd', $div)->item(0);

            $this->assertNotNull($nilai, 'Baris surel sekretariat harus memiliki nilai <dd>.');

            $kelasNilai = $nilai->getAttribute('class');

            $this->assertStringContainsString('min-w-0', $kelasNilai, 'Nilai tanpa `min-w-0` memaksa kartu sekretariat melebar ke kanan.');
            $this->assertStringContainsString('wrap-anywhere', $kelasNilai, 'Surel panjang harus boleh dipatahkan.');
        }
    }

    /**
     * Narasi profil Tentang Kami dan kartu sekretariat ditumpuk atas–bawah,
     * bukan dua kolom.
     *
     * Regresi: deskripsi organisasi dapat sangat panjang, sedangkan kartu
     * sekretariat relatif pendek. Kisi dua kolom lama menyisakan ruang kosong
     * besar di sisi kanan bagian profil.
     *
     * `min-w-0` tetap wajib pada setiap blok dan kolom kartu: kartu sekretariat
     * berisi surel yang tak bisa dipatahkan (±265px pada 16px, ±336px saat
     * ukuran teks sistem diperbesar), sehingga tanpa `min-width: 0` isi dapat
     * memaksa kontainer melebar melewati padding kanan `.container-page`.
     */
    public function test_bagian_profil_tentang_menumpuk_narasi_dan_sekretariat(): void
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get('/tentang')->assertOk()->getContent());
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $bagian = $xpath->query(
            '//section[.//p[contains(concat(" ", normalize-space(@class), " "), " section-eyebrow ") and normalize-space()="Sekilas Organisasi"]]',
        )->item(0);

        $this->assertNotNull($bagian, 'Bagian Sekilas Organisasi tidak ditemukan pada /tentang.');

        $kontainer = $xpath->query('./div[contains(concat(" ", normalize-space(@class), " "), " container-page ")]', $bagian)->item(0);

        $this->assertNotNull($kontainer, 'Kontainer bagian profil tidak ditemukan.');

        $kelasKontainer = preg_split('/\s+/', trim($kontainer->getAttribute('class'))) ?: [];

        $this->assertNotContains(
            'grid',
            $kelasKontainer,
            'Bagian profil harus menumpuk narasi dan sekretariat, bukan memakai kisi dua kolom.',
        );
        $this->assertStringNotContainsString(
            'grid-cols',
            $kontainer->getAttribute('class'),
            'Bagian profil tidak boleh memakai kolom kisi yang menyisakan ruang kosong di kanan.',
        );

        $narasi = $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " prose-perwama ")]', $kontainer)->item(0);
        $sekretariat = $xpath->query('.//p[normalize-space()="Sekretariat"]', $kontainer)->item(0);

        $this->assertNotNull($narasi, 'Narasi profil tidak ditemukan pada bagian Sekilas Organisasi.');
        $this->assertNotNull($sekretariat, 'Kartu sekretariat tidak ditemukan pada bagian Sekilas Organisasi.');

        $this->assertGreaterThan(
            0,
            $xpath->query('preceding::*[contains(concat(" ", normalize-space(@class), " "), " prose-perwama ")]', $sekretariat)->length,
            'Kartu sekretariat harus berada setelah narasi profil (tersusun atas–bawah).',
        );

        $blokNarasi = $narasi->parentNode;
        $kartu = $xpath->query('ancestor::div[contains(concat(" ", normalize-space(@class), " "), " rounded-2xl ")][1]', $sekretariat)->item(0);

        $this->assertNotNull($blokNarasi, 'Pembungkus narasi profil tidak ditemukan.');
        $this->assertNotNull($kartu, 'Kartu sekretariat tidak memiliki pembungkus.');

        foreach ([$blokNarasi, $kartu] as $blok) {
            $kelas = preg_split('/\s+/', trim($blok->getAttribute('class'))) ?: [];

            $this->assertContains(
                'min-w-0',
                $kelas,
                'Setiap blok bagian profil wajib `min-w-0` agar tidak melebar di layar sempit.',
            );
        }

        $kelasNarasi = preg_split('/\s+/', trim($blokNarasi->getAttribute('class'))) ?: [];

        $this->assertNotContains(
            'max-w-4xl',
            $kelasNarasi,
            'Blok narasi harus selebar kontainer; `max-w-4xl` membuat isi berhenti di ±75% dan menyisakan ruang kosong di kanan.',
        );

        $kolomKartu = $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " grid ") and contains(@class, "md:grid-cols-2")]/*', $kartu);

        $this->assertGreaterThanOrEqual(2, $kolomKartu->length, 'Kartu sekretariat harus berisi kolom alamat dan kontak.');

        foreach ($kolomKartu as $kolom) {
            $kelas = preg_split('/\s+/', trim($kolom->getAttribute('class'))) ?: [];

            $this->assertContains(
                'min-w-0',
                $kelas,
                'Setiap kolom kartu sekretariat wajib `min-w-0`, jika tidak trek kisi melebar melewati padding kanan.',
            );
        }
    }

    /**
     * Dropdown (<select>) formulir memakai chevron sendiri, bukan panah
     * bawaan peramban yang menempel di tepi kanan kotak.
     *
     * Regresi: panah bawaan hanya menyisakan ±4px dari garis tepi sementara
     * teks di kiri berjarak 1rem, sehingga ikon tampak rapat dan tidak
     * konsisten antar peramban. Chevron SVG kini diposisikan 1rem dari tepi
     * kanan dengan ruang teks 2.75rem agar pilihan panjang tidak menimpanya.
     */
    public function test_dropdown_formulir_memakai_chevron_dengan_jarak_dari_tepi_kanan(): void
    {
        $response = $this->get('/bergabung')->assertOk();

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());
        libxml_clear_errors();

        $dropdown = (new DOMXPath($dom))->query('//form//select');

        $this->assertSame(
            3,
            $dropdown->length,
            'Jumlah dropdown pada formulir pendaftaran dan mitra berubah; periksa apakah ada dropdown baru yang belum tertata.',
        );

        foreach ($dropdown as $select) {
            $kelas = preg_split('/\s+/', trim($select->getAttribute('class'))) ?: [];

            $this->assertContains(
                'field-input',
                $kelas,
                sprintf('Dropdown #%s harus memakai kelas .field-input agar chevron formulir ikut tertata.', $select->getAttribute('id')),
            );
        }

        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression(
            '/select\.field-input\s*\{[^}]*appearance:\s*none;/s',
            $css,
            'Panah bawaan <select> harus dimatikan agar posisi chevron bisa diatur sendiri.',
        );
        $this->assertMatchesRegularExpression(
            '/select\.field-input\s*\{[^}]*padding-right:\s*2\.75rem;/s',
            $css,
            'Dropdown butuh ruang kanan 2.75rem supaya teks pilihan tidak menimpa chevron.',
        );
        $this->assertMatchesRegularExpression(
            '/select\.field-input\s*\{[^}]*background-position:\s*right 1rem center;/s',
            $css,
            'Chevron harus berjarak 1rem dari tepi kanan — sejajar padding teks 1rem di sisi kiri.',
        );
    }

    /**
     * Daftar URL publik yang harus dirender, memakai slug hasil seeder.
     *
     * @return array<int, string>
     */
    private function publicUrls(): array
    {
        $slug = [
            'program' => Program::query()->value('slug'),
            'provinsi' => Region::query()->whereNull('parent_id')->value('slug'),
            'kabupaten' => Region::query()->whereNotNull('parent_id')->value('slug'),
            'kegiatan' => Activity::query()->value('slug'),
            'agenda' => Event::query()->value('slug'),
            'cerita' => Story::query()->value('slug'),
            'berita' => News::query()->value('slug'),
            'publikasi' => Publication::query()->value('slug'),
            'galeri' => Gallery::query()->value('slug'),
            'video' => Video::query()->value('slug'),
        ];

        foreach ($slug as $jenis => $nilai) {
            $this->assertNotNull($nilai, "Seeder belum mengisi konten untuk: {$jenis}.");
        }

        return [
            '/',
            '/tentang',
            '/program',
            '/program?kategori=pendidikan',
            '/program?q=beasiswa',
            '/program/'.$slug['program'],
            '/wilayah',
            '/wilayah/'.$slug['provinsi'],
            '/wilayah/'.$slug['kabupaten'],
            '/kegiatan',
            '/kegiatan?tab=agenda',
            '/kegiatan?kategori=pelatihan',
            '/kegiatan/'.$slug['kegiatan'],
            '/kegiatan/agenda/'.$slug['agenda'],
            '/cerita',
            '/cerita?kategori=tokoh_perempuan',
            '/cerita/'.$slug['cerita'],
            '/berita',
            '/berita?kategori=pendidikan',
            '/berita?tipe=opini',
            '/berita?q=beasiswa',
            '/berita/'.$slug['berita'],
            '/publikasi',
            '/publikasi?kategori=laporan',
            '/publikasi/'.$slug['publikasi'],
            '/galeri',
            '/galeri?album=pelatihan',
            '/galeri/'.$slug['galeri'],
            '/galeri/video',
            '/galeri/video?kategori=profil',
            '/galeri/video/'.$slug['video'],
            '/bergabung',
            '/kontak',
            '/cari',
            '/cari?q=perempuan',
            '/program?page=2',
            '/berita?page=2',
            '/publikasi?page=2',
        ];
    }
}
