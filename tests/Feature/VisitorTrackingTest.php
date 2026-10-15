<?php

namespace Tests\Feature;

use App\Models\OrganizationProfile;
use App\Models\PageView;
use App\Models\PanelSetting;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Support\VisitorStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Pencatatan kunjungan portal publik.
 *
 * Angka pada bagian "Ringkasan Pengunjung Website" di dasbor panel berasal dari
 * tabel `page_views`; uji ini menjaga agar kunjungan nyata tercatat, sedangkan
 * lalu lintas panel, pengelola, perayap, dan halaman galat tidak ikut terhitung.
 */
class VisitorTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pengaturan panel & situs memakai cache statis; dibersihkan agar tiap uji berdiri sendiri.
        PanelSetting::flushCache();
        WebsiteSetting::flushCache();
        OrganizationProfile::flushCache();
    }

    /**
     * Halaman publik yang berhasil dibuka dicatat sebagai satu kunjungan.
     */
    public function test_kunjungan_halaman_publik_dicatat(): void
    {
        $this->get('/')->assertOk();

        $kunjungan = PageView::query()->firstOrFail();

        $this->assertSame('/', $kunjungan->path);
        $this->assertSame('home', $kunjungan->route_name);
        $this->assertSame(64, strlen($kunjungan->visitor_hash));
        $this->assertNotNull($kunjungan->session_id);
        $this->assertNull($kunjungan->duration_seconds);
    }

    /**
     * Halaman panel dan kunjungan pengelola yang sedang masuk tidak dihitung
     * sebagai pengunjung situs.
     */
    public function test_panel_dan_pengelola_tidak_dihitung_sebagai_pengunjung(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/')->assertOk();

        $this->assertDatabaseCount('page_views', 0);
    }

    /**
     * Permintaan POST dan halaman galat bukan kunjungan halaman.
     */
    public function test_permintaan_post_dan_halaman_galat_tidak_dicatat(): void
    {
        $this->post('/kontak')->assertStatus(302);
        $this->get('/halaman-yang-tidak-ada')->assertNotFound();

        $this->assertDatabaseCount('page_views', 0);
    }

    /**
     * Perayap mesin pencari tidak ikut dihitung sebagai pengunjung.
     */
    public function test_perayap_tidak_dihitung(): void
    {
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])
            ->get('/')
            ->assertOk();

        $this->assertDatabaseCount('page_views', 0);
    }

    /**
     * Pengunjung unik dihitung dari sidik jari peramban (IP + user agent),
     * bukan dari jumlah halaman yang dibuka.
     */
    public function test_pengunjung_unik_dihitung_per_peramban(): void
    {
        $this->withHeaders(['User-Agent' => 'Peramban Uji Satu'])->get('/')->assertOk();
        $this->withHeaders(['User-Agent' => 'Peramban Uji Satu'])->get('/kontak')->assertOk();
        $this->withHeaders(['User-Agent' => 'Peramban Uji Dua'])->get('/')->assertOk();

        [$mulai, $akhir] = rentang_bulan();

        $ringkasan = VisitorStatistics::summary($mulai, $akhir);

        $this->assertSame(3, $ringkasan['page_views']);
        $this->assertSame(2, $ringkasan['unique_visitors']);
    }

    /**
     * Laporan lama kunjungan dari peramban melengkapi catatan kunjungan, dan
     * laporan yang lebih pendek tidak menurunkan angka yang sudah tercatat.
     */
    public function test_laporan_lama_kunjungan_menyimpan_angka_terbesar(): void
    {
        $this->get('/')->assertOk();

        $kunjungan = PageView::query()->firstOrFail();

        $this->post(route('visit.duration'), ['duration' => 165])->assertOk();
        $this->assertSame(165, $kunjungan->fresh()->duration_seconds);

        $this->post(route('visit.duration'), ['duration' => 30])->assertOk();
        $this->assertSame(165, $kunjungan->fresh()->duration_seconds);
    }

    /**
     * Laporan lama kunjungan tetap diterima setelah sesi pengunjung berakhir,
     * selama masih kunjungan peramban yang sama.
     */
    public function test_laporan_tetap_diterima_bila_sesi_berakhir(): void
    {
        $this->get('/')->assertOk();

        $kunjungan = PageView::query()->firstOrFail();

        // Sesi dianggap tidak lagi dikenali (mis. cookie sesi sudah berubah).
        $kunjungan->update(['session_id' => 'sesi-yang-sudah-tidak-dipakai']);

        $this->post(route('visit.duration'), ['duration' => 90])->assertOk();

        $this->assertSame(90, $kunjungan->fresh()->duration_seconds);
    }

    /**
     * Rata-rata waktu akses hanya dihitung dari kunjungan yang dilaporkan,
     * sedangkan trafik harian dikelompokkan per tanggal.
     */
    public function test_rata_rata_waktu_akses_dan_trafik_harian(): void
    {
        // Rentang tetap agar hasilnya tidak bergantung tanggal uji dijalankan.
        [$mulai, $akhir] = rentang_bulan(Carbon::parse('2026-03-01'));

        $this->kunjungan(Carbon::parse('2026-03-02 09:00'), durasi: 120);
        $this->kunjungan(Carbon::parse('2026-03-02 10:00'), durasi: 180);
        $this->kunjungan(Carbon::parse('2026-03-05 11:00'));

        $ringkasan = VisitorStatistics::summary($mulai, $akhir);

        $this->assertSame(3, $ringkasan['page_views']);
        $this->assertEqualsWithDelta(150.0, $ringkasan['average_seconds'], 0.01);

        $perHari = VisitorStatistics::dailyPageViews($mulai, $akhir);

        $this->assertSame(2, $perHari['2026-03-02']);
        $this->assertSame(1, $perHari['2026-03-05']);
    }

    /**
     * Kunjungan pada bulan lain tidak ikut terhitung.
     */
    public function test_kunjungan_bulan_lain_tidak_terhitung(): void
    {
        [$mulai, $akhir] = rentang_bulan(Carbon::parse('2026-03-01'));

        $this->kunjungan(Carbon::parse('2026-02-28 23:00'));
        $this->kunjungan(Carbon::parse('2026-04-01 00:30'));

        $this->assertSame(0, VisitorStatistics::summary($mulai, $akhir)['page_views']);
    }

    private function kunjungan(Carbon $waktu, ?int $durasi = null): void
    {
        PageView::create([
            'path' => '/',
            'route_name' => 'home',
            'visitor_hash' => hash('sha256', 'pengunjung-uji'),
            'session_id' => 'sesi-uji',
            'viewed_at' => $waktu,
            'duration_seconds' => $durasi,
        ]);
    }
}
