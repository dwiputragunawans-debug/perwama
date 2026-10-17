<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\Management;
use App\Models\OrganizationHistory;
use App\Models\OrganizationProfile;
use App\Models\PanelSetting;
use App\Models\ProgramCategory;
use App\Models\Statistic;
use App\Models\WebsiteSetting;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tata letak halaman Tentang Kami.
 *
 * Halaman memakai hero bersama <x-page-hero> seperti halaman lain (kecuali
 * beranda), lalu sekilas organisasi yang ditutup pita statistik, visi & misi,
 * bidang pengabdian, sejarah bertab, serta struktur pengurus bertab — dengan
 * warna, tipografi, dan komponen tema situs yang sudah ada.
 */
class AboutPageLayoutTest extends TestCase
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
     * Hero memakai komponen bersama <x-page-hero> seperti halaman lain: eyebrow
     * bergaris emas, nama organisasi dengan ringkasan profil, foto profil
     * sebagai latar tipis, dan breadcrumb. Tombol anchor khusus halaman ini
     * (pada hero dua kolom yang lama) sudah tidak dipakai lagi.
     */
    public function test_hero_memakai_komponen_halaman_seperti_halaman_lain(): void
    {
        $profil = OrganizationProfile::current();
        $profil->update(['about_card_image' => 'uploads/about-cards/tentang.jpg']);
        OrganizationProfile::flushCache();

        $xpath = $this->xpath('/tentang');
        $hero = $this->bagian($xpath, 'Profil Organisasi');

        $this->assertNotNull($hero, 'Bagian hero tidak ditemukan pada halaman Tentang.');

        // Foto profil organisasi dipakai sebagai latar hero, seperti halaman
        // detail lain yang memakai foto kontennya sendiri.
        $this->assertSame(
            1,
            $xpath->query('.//img[contains(@src, "uploads/about-cards/tentang.jpg")]', $hero)->length,
            'Foto profil organisasi harus dipakai pada hero.',
        );

        // Satu <h1>: nama organisasi, dengan ringkasan profil sebagai pengantar.
        $this->assertSame(1, $xpath->query('.//h1', $hero)->length, 'Hero memakai tepat satu <h1>.');
        $this->assertStringContainsString($profil->name, $hero->textContent);
        $this->assertStringContainsString($profil->short_description, $hero->textContent);
        $this->assertStringNotContainsString('Lihat Visi &amp; Misi', $hero->textContent);

        // Eyebrow bergaris emas dan breadcrumb: pola hero halaman lain.
        $this->assertSame(
            1,
            $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " gold-rule ")]', $hero)->length,
            'Hero memakai garis emas di atas eyebrow.',
        );
        $this->assertSame(1, $xpath->query('.//nav[@aria-label="Breadcrumb"]', $hero)->length);
    }

    /**
     * Karena memakai komponen yang sama, pembungkus dan tipografi hero halaman
     * Tentang identik dengan hero halaman lain (dibandingkan dengan Program).
     */
    public function test_hero_sebentuk_dengan_hero_halaman_program(): void
    {
        $tentang = $this->bagian($this->xpath('/tentang'), 'Profil Organisasi');
        $program = $this->bagian($this->xpath('/program'), 'Program & Kegiatan PERWAMA INA');

        $this->assertNotNull($tentang, 'Hero halaman Tentang tidak ditemukan.');
        $this->assertNotNull($program, 'Hero halaman Program tidak ditemukan.');

        $this->assertSame(
            $program->getAttribute('class'),
            $tentang->getAttribute('class'),
            'Hero Tentang harus memakai pembungkus yang sama dengan halaman lain.',
        );

        $h1Tentang = (new DOMXPath($tentang->ownerDocument))->query('.//h1', $tentang)->item(0);
        $h1Program = (new DOMXPath($program->ownerDocument))->query('.//h1', $program)->item(0);

        $this->assertNotNull($h1Tentang);
        $this->assertNotNull($h1Program);
        $this->assertSame($h1Program->getAttribute('class'), $h1Tentang->getAttribute('class'));
    }

    /**
     * Bila foto khusus belum diunggah, cover galeri pertama dipakai agar bagian
     * hero tidak kosong.
     */
    public function test_hero_memakai_cover_galeri_bila_foto_khusus_kosong(): void
    {
        OrganizationProfile::current()->update(['about_card_image' => null]);
        OrganizationProfile::flushCache();

        $cover = Gallery::query()->active()->ordered()->value('cover_image');

        $this->assertNotNull($cover, 'Seeder harus mengisi cover galeri sebagai cadangan foto hero.');

        $this->get('/tentang')
            ->assertOk()
            ->assertSee($cover, false);
    }

    /**
     * Label "Profil Organisasi" hanya muncul sekali (pada hero) dan bagian
     * profil memakai label lain, sehingga tidak ada label yang berulang.
     */
    public function test_label_profil_organisasi_tidak_berulang(): void
    {
        $html = $this->get('/tentang')->assertOk()->getContent();

        $this->assertSame(
            1,
            substr_count($html, '>Profil Organisasi<'),
            'Label "Profil Organisasi" hanya boleh tampil sekali (pada hero).',
        );

        $this->assertStringContainsString('Sekilas Organisasi', $html);
    }

    /**
     * Breadcrumb hero memakai pola halaman lain: "Beranda" sebagai tautan,
     * lalu halaman yang sedang dibuka sebagai teks aktif.
     */
    public function test_breadcrumb_hero_memakai_pola_halaman_lain(): void
    {
        $breadcrumb = $this->xpath('/tentang')->query('//nav[@aria-label="Breadcrumb"]')->item(0);

        $this->assertNotNull($breadcrumb, 'Breadcrumb tidak ditemukan pada hero halaman Tentang.');

        $teks = trim((string) preg_replace('/\s+/', ' ', $breadcrumb->textContent));

        $this->assertSame('Beranda / Tentang Kami', $teks);

        $beranda = (new DOMXPath($breadcrumb->ownerDocument))->query('.//a', $breadcrumb)->item(0);

        $this->assertNotNull($beranda, 'Breadcrumb harus memuat tautan Beranda.');
        $this->assertSame(route('home'), $beranda->getAttribute('href'));
    }

    /**
     * Pita statistik bergaya kartu putih dipertahankan, tetapi kini menutup
     * bagian Sekilas Organisasi: empat angka utama pada kartu melintang penuh,
     * sedangkan angka kelima dan seterusnya tampil pada kartu "Capaian lainnya"
     * di samping profil agar tidak ada data yang hilang.
     */
    public function test_pita_statistik_menutup_bagian_sekilas_organisasi(): void
    {
        $statistik = Statistic::query()->active()->ordered()->get();

        $this->assertGreaterThanOrEqual(5, $statistik->count(), 'Seeder harus mengisi lebih dari empat angka organisasi.');

        $xpath = $this->xpath('/tentang');
        $pita = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " stat-band ")]')->item(0);

        $this->assertNotNull($pita, 'Pita statistik tidak ditemukan pada halaman Tentang.');

        foreach ($statistik->take(4) as $stat) {
            $this->assertStringContainsString((string) $stat->value, $pita->textContent);
            $this->assertStringContainsString($stat->label, $pita->textContent);
        }

        // Angka kelima dan seterusnya tidak ikut pada pita, tetapi tetap tampil.
        $this->assertStringNotContainsString($statistik[4]->label, $pita->textContent);
        $this->assertStringContainsString('Capaian lainnya', $xpath->document->textContent);
        $this->assertStringContainsString($statistik[4]->label, $xpath->document->textContent);

        // Pita berada di dalam bagian Sekilas Organisasi, di bawah narasi profil.
        $sekilas = $this->bagian($xpath, 'Sekilas Organisasi');

        $this->assertNotNull($sekilas, 'Bagian Sekilas Organisasi tidak ditemukan.');
        $this->assertSame(
            1,
            $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " stat-band ")]', $sekilas)->length,
            'Pita statistik harus berada di dalam bagian Sekilas Organisasi.',
        );
        $this->assertGreaterThan(
            0,
            $xpath->query('preceding::*[contains(concat(" ", normalize-space(@class), " "), " prose-perwama ")]', $pita)->length,
            'Pita statistik berada di dasar bagian, setelah narasi profil.',
        );
    }

    /**
     * Tagline dan tahun berdiri tetap tampil — kini sebagai keterangan pada
     * bagian Sekilas Organisasi, bukan lencana pada foto hero.
     */
    public function test_tagline_dan_tahun_berdiri_tampil_pada_sekilas_organisasi(): void
    {
        $profil = OrganizationProfile::current();
        $sekilas = $this->bagian($this->xpath('/tentang'), 'Sekilas Organisasi');

        $this->assertNotNull($sekilas, 'Bagian Sekilas Organisasi tidak ditemukan.');

        $this->assertStringContainsString($profil->tagline, $sekilas->textContent);
        $this->assertStringContainsString('Berdiri sejak '.$profil->founded_year, $sekilas->textContent);
    }

    /**
     * Visi tampil sebagai kutipan pada kartu navy dan misi sebagai daftar
     * bernomor, lengkap dengan seluruh butir misi dari profil organisasi.
     */
    public function test_visi_misi_tampil_sebagai_kutipan_dan_daftar_bernomor(): void
    {
        $profil = OrganizationProfile::current();

        $xpath = $this->xpath('/tentang');
        $bagian = $xpath->query('//*[@id="visi-misi"]')->item(0);

        $this->assertNotNull($bagian, 'Bagian Visi & Misi tidak ditemukan.');

        $this->assertStringContainsString('Visi & Misi Organisasi', $bagian->textContent);
        $this->assertStringContainsString('Visi Utama', $bagian->textContent);
        $this->assertStringContainsString($profil->vision, $bagian->textContent);
        $this->assertStringContainsString('Misi Strategi Organisasi', $bagian->textContent);

        // Satu butir misi = satu nomor; teksnya tampil utuh.
        $butir = $xpath->query('.//ol/li', $bagian);

        $this->assertSame(count($profil->mission), $butir->length);

        foreach ($profil->mission as $index => $misi) {
            $this->assertStringContainsString($misi, $butir->item($index)->textContent);
            $this->assertStringContainsString((string) ($index + 1), $butir->item($index)->textContent);
        }
    }

    /**
     * Bidang pengabdian mengikuti kategori program: satu kartu per kategori,
     * memakai warna kategori dan tautan ke daftar program kategori tersebut.
     */
    public function test_bidang_pengabdian_memakai_kategori_program(): void
    {
        $kategori = ProgramCategory::query()->active()->ordered()->get();

        $this->assertNotEmpty($kategori, 'Seeder harus mengisi kategori program.');

        $xpath = $this->xpath('/tentang');
        $bagian = $this->bagian($xpath, 'Bidang Pengabdian Utama');

        $this->assertNotNull($bagian, 'Bagian Bidang Pengabdian Utama tidak ditemukan.');
        $this->assertStringContainsString('Fokus Gerakan', $bagian->textContent);

        $kartu = $xpath->query('.//a[contains(concat(" ", normalize-space(@class), " "), " card ")]', $bagian);

        $this->assertSame(
            $kategori->count(),
            $kartu->length,
            'Jumlah kartu bidang pengabdian harus sama dengan jumlah kategori program.',
        );

        foreach ($kategori as $index => $item) {
            $this->assertStringContainsString($item->name, $kartu->item($index)->textContent);

            // Warna bidang dipakai pada kepala kartu (mengikuti warna kategori).
            $kepala = $xpath->query('.//*[@style]', $kartu->item($index))->item(0);

            $this->assertNotNull($kepala, "Kepala kartu bidang {$item->name} tidak ditemukan.");
            $this->assertStringContainsString($item->color, $kepala->getAttribute('style'));

            $this->assertStringContainsString(
                route('program.index', ['kategori' => $item->slug]),
                $kartu->item($index)->getAttribute('href'),
            );
        }
    }

    /**
     * Struktur pengurus memakai tab per tingkat, dan kartu pertama tiap tingkat
     * (pengurus dengan Urutan terkecil) tampil lebih besar daripada pengurus
     * lainnya.
     */
    public function test_struktur_pengurus_memakai_tab_per_tingkat(): void
    {
        $xpath = $this->xpath('/tentang');

        // Tab pengurus berada pada wadah [data-tabs] tersendiri, terpisah dari
        // tab tahun pada bagian Sejarah.
        $wadah = $xpath->query('//*[@data-tabs][.//*[@data-tab="dpp"]]')->item(0);

        $this->assertNotNull($wadah, 'Struktur pengurus memakai wadah tab tersendiri.');

        $tab = $xpath->query('.//*[@role="tab"]', $wadah);

        $this->assertSame(3, $tab->length, 'Setiap tingkat kepengurusan memakai satu tab.');
        $this->assertSame(
            1,
            $xpath->query('.//*[@role="tab" and contains(@class, "is-active")]', $wadah)->length,
            'Hanya satu tab yang boleh aktif saat halaman dibuka.',
        );
        $this->assertSame(
            'true',
            $tab->item(0)->getAttribute('aria-selected'),
            'Tab pertama (DPP) aktif saat halaman dibuka.',
        );

        $panel = $xpath->query('.//*[@data-tab-panel]', $wadah);

        $this->assertSame(3, $panel->length, 'Setiap tingkat kepengurusan memiliki satu panel.');

        foreach (['dpp', 'dpw', 'pc'] as $index => $tingkat) {
            $this->assertSame(
                $tingkat,
                $panel->item($index)->getAttribute('data-tab-panel'),
                'Urutan panel mengikuti tingkat kepengurusan.',
            );
        }

        // Kartu pertama panel adalah pengurus dengan Urutan terkecil; pada data
        // contoh kebetulan ketua tingkat. Pengurus lain menyusul.
        foreach ([['dpp', 'Ketua Umum'], ['dpw', 'Ketua DPW Maluku'], ['pc', 'Ketua PC Kota Ambon']] as [$tingkat, $jabatan]) {
            $panelTingkat = $xpath->query("//*[@data-tab-panel=\"{$tingkat}\"]")->item(0);

            $this->assertNotNull($panelTingkat, "Panel tingkat {$tingkat} tidak ditemukan.");

            $ketua = $xpath->query('.//article', $panelTingkat)->item(0);

            $this->assertNotNull($ketua, "Kartu ketua untuk tingkat {$tingkat} tidak ditemukan.");
            $this->assertStringContainsString($jabatan, $ketua->textContent);

            $namaKetua = Management::query()
                ->active()
                ->where('level', $tingkat)
                ->where('position', $jabatan)
                ->value('name');

            $this->assertStringContainsString($namaKetua, $ketua->textContent);
        }
    }

    /**
     * Urutan kartu pengurus di situs sepenuhnya mengikuti kolom Urutan di panel
     * admin (Pengurus → Urutan), termasuk pada kartu pertama yang tampil lebih
     * besar. Dulu kartu utama dipilih dari "orang pertama yang jabatannya
     * mengandung kata ketua", sehingga susunan yang sudah diatur admin — mis.
     * Penasehat (1), Ketua Bidang III (2), Ketua Umum (3) — tidak dipakai.
     */
    public function test_urutan_kartu_pengurus_mengikuti_kolom_urutan_di_panel_admin(): void
    {
        // Susunan seperti data pada panel admin: Penasehat (Urutan 1),
        // Ketua Bidang III (Urutan 2), baru kemudian Ketua Umum (Urutan 3).
        Management::query()->where('level', 'dpp')->where('position', 'Wakil Ketua Umum')
            ->update(['position' => 'Penasehat', 'sort_order' => 1]);

        Management::query()->where('level', 'dpp')->where('position', 'Sekretaris Jenderal')
            ->update(['position' => 'Ketua Bidang III', 'sort_order' => 2]);

        Management::query()->where('level', 'dpp')->where('position', 'Ketua Umum')
            ->update(['sort_order' => 3]);

        $xpath = $this->xpath('/tentang');

        foreach (['dpp', 'dpw', 'pc'] as $tingkat) {
            $panel = $xpath->query("//*[@data-tab-panel=\"{$tingkat}\"]")->item(0);

            $this->assertNotNull($panel, "Panel tingkat {$tingkat} tidak ditemukan.");

            $kartu = $xpath->query('.//article', $panel);
            $nama = Management::query()
                ->active()
                ->where('level', $tingkat)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('name');

            $this->assertSame(
                $nama->count(),
                $kartu->length,
                "Setiap pengurus tingkat {$tingkat} tampil tepat satu kartu.",
            );

            foreach ($nama->values() as $index => $satu) {
                $this->assertStringContainsString(
                    $satu,
                    $kartu->item($index)->textContent,
                    'Kartu ke-'.($index + 1)." tingkat {$tingkat} harus berisi {$satu} sesuai kolom Urutan.",
                );
            }
        }

        // Kartu pertama DPP adalah pengurus dengan Urutan 1 (Penasehat), bukan
        // Ketua Umum yang Urutan-nya 3.
        $pertama = $xpath->query('//*[@data-tab-panel="dpp"]//article')->item(0);

        $this->assertNotNull($pertama, 'Kartu pertama tingkat DPP tidak ditemukan.');
        $this->assertStringContainsString('Martha Latuconsina, M.Pd.', $pertama->textContent);
        $this->assertStringContainsString('Penasehat', $pertama->textContent);
    }

    /**
     * Bagian kepemimpinan memakai latar halaman yang sama dengan bagian Fokus
     * Gerakan (tanpa pita .section-soft), sehingga tampil menyatu dengan bagian
     * sebelum penutup halaman. Pita .section-soft tetap dipakai bagian Visi &
     * Misi dan Sejarah sebagai pemisah ritme.
     */
    public function test_kepemimpinan_memakai_latar_sama_dengan_fokus_gerakan(): void
    {
        $xpath = $this->xpath('/tentang');

        $fokus = $this->bagian($xpath, 'Fokus Gerakan');
        $kepemimpinan = $xpath->query('//*[@id="struktur-pengurus"]')->item(0);

        $this->assertNotNull($fokus, 'Bagian Fokus Gerakan tidak ditemukan.');
        $this->assertNotNull($kepemimpinan, 'Bagian kepemimpinan tidak ditemukan.');

        $this->assertSame(
            $fokus->getAttribute('class'),
            $kepemimpinan->getAttribute('class'),
            'Latar bagian kepemimpinan harus sama dengan bagian Fokus Gerakan.',
        );
        $this->assertStringNotContainsString('section-soft', $kepemimpinan->getAttribute('class'));

        // Dua pita .section-soft lain pada halaman tetap dipertahankan.
        foreach (['//*[@id="visi-misi"]', '//*[@id="sejarah"]'] as $jalur) {
            $bagian = $xpath->query($jalur)->item(0);

            $this->assertNotNull($bagian, "Bagian {$jalur} tidak ditemukan.");
            $this->assertStringContainsString('section-soft', $bagian->getAttribute('class'));
        }
    }

    /**
     * Keterangan masa jabatan memakai satu istilah saja — "Periode" — baik pada
     * kartu ketua maupun kartu pengurus lainnya, sehingga tidak ada lagi kartu
     * yang memakai "Masa bakti" dan kartu lain memakai "Periode".
     */
    public function test_keterangan_masa_jabatan_pengurus_memakai_satu_istilah(): void
    {
        $periode = Management::query()
            ->active()
            ->whereNotNull('period')
            ->distinct()
            ->pluck('period');

        $this->assertNotEmpty($periode, 'Data pengurus harus memiliki periode untuk diuji.');

        $response = $this->get('/tentang')->assertOk();

        foreach ($periode as $nilai) {
            $response->assertSee('Periode '.$nilai, false);
        }

        $response->assertDontSee('Masa bakti', false);
        $response->assertDontSee('Masa Bakti', false);
    }

    /**
     * Pengurus yang belum memiliki foto memakai bulatan inisial berwarna palet
     * situs (bukan gambar kosong), dengan warna teks eksplisit.
     */
    public function test_pengurus_tanpa_foto_memakai_bulatan_inisial(): void
    {
        Management::query()
            ->active()
            ->where('level', 'dpp')
            ->where('position', 'Ketua Umum')
            ->update(['photo' => null]);

        $xpath = $this->xpath('/tentang');
        $ketua = $xpath->query('//*[@data-tab-panel="dpp"]//article')->item(0);

        $this->assertNotNull($ketua);

        $inisial = $xpath->query(
            './/*[contains(concat(" ", normalize-space(@class), " "), " rounded-full ")'
            .' and contains(concat(" ", normalize-space(@class), " "), " bg-navy ")]',
            $ketua,
        );

        $this->assertSame(1, $inisial->length, 'Pengurus tanpa foto harus memakai bulatan inisial.');
        $this->assertStringContainsString('R', $inisial->item(0)->textContent);
        $this->assertStringContainsString('text-white', $inisial->item(0)->getAttribute('class'));
    }

    /**
     * Sejarah organisasi dan ajakan penutup tetap tampil pada halaman.
     */
    public function test_sejarah_dan_ajakan_penutup_tetap_tampil(): void
    {
        $sejarah = OrganizationHistory::query()->active()->ordered()->firstOrFail();

        $this->get('/tentang')
            ->assertOk()
            ->assertSee('Sejarah')
            ->assertSee('Perjalanan PERWAMA INA')
            ->assertSee($sejarah->title)
            ->assertSee('Ingin tahu lebih banyak?');
    }

    /**
     * Sejarah memakai tab tahun (pola .chip yang sama dengan Struktur Pengurus)
     * dan tiap peristiwa tampil sebagai satu kartu: lencana tahun & lokasi,
     * judul, deskripsi, baris lokasi berpeta, serta sisi foto.
     */
    public function test_sejarah_memakai_tab_tahun_dan_kartu_peristiwa(): void
    {
        $sejarah = OrganizationHistory::query()->active()->ordered()->firstOrFail();
        $jumlah = OrganizationHistory::query()->active()->count();

        $xpath = $this->xpath('/tentang');

        // Satu tab per peristiwa; hanya tab pertama yang aktif saat halaman dibuka.
        $tab = $xpath->query('//*[@data-tab and starts-with(@data-tab, "sejarah-")]');
        $this->assertSame($jumlah, $tab->length, 'Setiap peristiwa sejarah harus punya tab tahun.');

        $this->assertSame(
            1,
            $xpath->query(
                '//*[@data-tab and starts-with(@data-tab, "sejarah-")'
                .' and contains(concat(" ", normalize-space(@class), " "), " is-active ")]',
            )->length,
            'Hanya tab tahun pertama yang aktif saat halaman dimuat.',
        );

        $this->assertSame(
            $jumlah,
            $xpath->query('//*[@data-tab-panel and starts-with(@data-tab-panel, "sejarah-")]')->length,
            'Panel tiap peristiwa sejarah harus tersedia (tetap tampil tanpa JavaScript).',
        );

        $tabPertama = $xpath->query('//*[@id="tab-sejarah-'.$sejarah->getKey().'"]')->item(0);

        $this->assertNotNull($tabPertama, 'Tab tahun peristiwa pertama harus ada.');
        $this->assertStringContainsString((string) $sejarah->year, $tabPertama->textContent);
        $this->assertSame('true', $tabPertama->getAttribute('aria-selected'));
        $this->assertSame(
            1,
            $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " chip-dot ")]', $tabPertama)->length,
            'Tab tahun memakai titik penanda gold.',
        );

        // Kartu peristiwa: lencana tahun & lokasi, judul, deskripsi, baris lokasi.
        $kartu = $xpath->query('//*[@data-tab-panel="sejarah-'.$sejarah->getKey().'"]//article')->item(0);

        $this->assertNotNull($kartu, 'Kartu peristiwa sejarah harus tampil.');
        $this->assertStringContainsString('history-card', $kartu->getAttribute('class'));

        $lencana = $xpath->query(
            './/*[contains(concat(" ", normalize-space(@class), " "), " history-badge ")]',
            $kartu,
        )->item(0);

        $this->assertNotNull($lencana, 'Kartu sejarah memakai lencana tahun.');
        $this->assertStringContainsString('Tahun '.$sejarah->year, $lencana->textContent);
        $this->assertStringContainsString((string) $sejarah->location, $lencana->textContent);

        $this->assertStringContainsString($sejarah->title, $kartu->textContent);

        $lokasi = $xpath->query(
            './/*[contains(concat(" ", normalize-space(@class), " "), " history-place ")]',
            $kartu,
        )->item(0);

        $this->assertNotNull($lokasi, 'Baris lokasi peristiwa harus tampil.');
        $this->assertStringContainsString('Lokasi peristiwa', $lokasi->textContent);
        $this->assertSame(1, $xpath->query('.//svg', $lokasi)->length, 'Baris lokasi memakai penanda peta.');

        $foto = $xpath->query(
            './/*[contains(concat(" ", normalize-space(@class), " "), " history-media ")]',
            $kartu,
        )->item(0);

        $this->assertNotNull($foto, 'Sisi foto peristiwa harus tersedia.');
        $this->assertSame(1, $xpath->query('.//img', $foto)->length, 'Foto peristiwa tampil pada sisi kanan kartu.');
    }

    /**
     * Bila foto peristiwa belum diunggah, sisi foto tetap terisi judul serta
     * tahun sehingga kartu tidak tampak kosong.
     */
    public function test_sejarah_menampilkan_judul_bila_foto_peristiwa_kosong(): void
    {
        $sejarah = OrganizationHistory::query()->active()->ordered()->firstOrFail();
        $sejarah->update(['image' => null, 'location' => null]);

        $kartu = $this->xpath('/tentang')
            ->query('//*[@data-tab-panel="sejarah-'.$sejarah->getKey().'"]//article')
            ->item(0);

        $this->assertNotNull($kartu);

        $sisi = (new DOMXPath($kartu->ownerDocument))->query(
            './/*[contains(concat(" ", normalize-space(@class), " "), " history-media ")]',
            $kartu,
        )->item(0);

        $this->assertNotNull($sisi);
        $this->assertSame(0, (new DOMXPath($kartu->ownerDocument))->query('.//img', $sisi)->length);
        $this->assertStringContainsString($sejarah->title, $sisi->textContent);

        // Lencana tetap tampil walau lokasi kosong.
        $this->assertInstanceOf(
            DOMElement::class,
            (new DOMXPath($kartu->ownerDocument))->query(
                './/*[contains(concat(" ", normalize-space(@class), " "), " history-badge ")]',
                $kartu,
            )->item(0),
        );
    }

    private function xpath(string $url): DOMXPath
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get($url)->assertOk()->getContent());
        libxml_clear_errors();

        return new DOMXPath($dom);
    }

    /**
     * Elemen <section> yang memuat teks tertentu.
     */
    private function bagian(DOMXPath $xpath, string $teks): ?DOMElement
    {
        foreach ($xpath->query('//main//section') as $section) {
            if (str_contains($section->textContent, $teks)) {
                return $section;
            }
        }

        return null;
    }
}
