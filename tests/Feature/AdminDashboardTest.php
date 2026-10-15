<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\DailyVisitorChart;
use App\Filament\Widgets\LatestMembersTable;
use App\Filament\Widgets\MemberGrowthChart;
use App\Filament\Widgets\OrganizationOverview;
use App\Filament\Widgets\VisitorOverview;
use App\Models\Activity;
use App\Models\Branch;
use App\Models\Event;
use App\Models\Member;
use App\Models\News;
use App\Models\OrganizationProfile;
use App\Models\PageView;
use App\Models\PanelSetting;
use App\Models\Region;
use App\Models\User;
use App\Models\WebsiteSetting;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Dasbor panel admin: "Ringkasan Pengunjung Website" (trafik portal publik) dan
 * "Ringkasan Organisasi" (anggota, wilayah, berita, kegiatan).
 */
class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pengaturan panel & situs memakai cache statis; dibersihkan agar tiap uji berdiri sendiri.
        PanelSetting::flushCache();
        WebsiteSetting::flushCache();
        OrganizationProfile::flushCache();

        /*
         * Widget dasbor memakai URL resource (mis. tautan "Lihat semua"),
         * sehingga panel admin perlu dijadikan panel aktif ketika widget diuji
         * di luar permintaan HTTP.
         */
        Filament::setCurrentPanel('admin');
    }

    /**
     * Dasbor menampilkan kedua bagian ringkasan beserta grafik dan daftarnya.
     */
    public function test_dasbor_menampilkan_ringkasan_pengunjung_dan_organisasi(): void
    {
        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Dasbor', false)
            ->assertSee('Ringkasan Pengunjung Website', false)
            ->assertSee('Pengunjung Unik', false)
            ->assertSee('Total Halaman Dilihat', false)
            ->assertSee('Rata-rata Waktu Akses', false)
            ->assertSee('Trafik Pengunjung Harian', false)
            ->assertSee('Bulan ini', false)
            ->assertSee('Bulan lalu', false)
            ->assertSee('Ringkasan Organisasi', false)
            ->assertSee('Total Anggota', false)
            ->assertSee('Wilayah / Cabang', false)
            ->assertSee('Berita Diterbitkan', false)
            ->assertSee('Total Kegiatan', false)
            ->assertSee('Pendaftaran Anggota Terbaru', false)
            ->assertSee('Pertumbuhan Anggota', false)
            ->assertSee('Lihat semua', false)
            /*
             * Tata letak: kartu statistik dan grafik trafik memakai lebar penuh
             * (`--col-span-lg: 1 / -1`), sedangkan daftar anggota terbaru
             * memakai dua dari tiga kolom dasbor.
             */
            ->assertSee('--col-span-lg: 1 / -1', false)
            ->assertSee('--col-span-lg: span 2 / span 2', false)
            ->assertSee('--col-span-lg: span 1 / span 1', false);
    }

    /**
     * Dasbor memakai tiga kolom (agar kartu statistik penuh sedangkan daftar
     * anggota dan grafik pertumbuhan berdampingan) dan susunan widgetnya tetap.
     */
    public function test_dasbor_memakai_tiga_kolom_dengan_urutan_widget_tetap(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertContains(Dashboard::class, $panel->getPages());
        $this->assertSame('Dasbor', Dashboard::getNavigationLabel());
        $this->assertSame(3, (new Dashboard)->getColumns());

        $this->assertSame([
            AccountWidget::class,
            VisitorOverview::class,
            DailyVisitorChart::class,
            OrganizationOverview::class,
            LatestMembersTable::class,
            MemberGrowthChart::class,
        ], array_values($panel->getWidgets()));
    }

    /**
     * Kartu "Ringkasan Pengunjung Website" menghitung kunjungan nyata bulan ini
     * dan membandingkannya dengan bulan sebelumnya.
     */
    public function test_kartu_pengunjung_menghitung_kunjungan_bulan_ini(): void
    {
        $this->kunjungan(now(), 2, 'pengunjung-a', 120);
        $this->kunjungan(now(), 2, 'pengunjung-b', 60);
        $this->kunjungan(now()->subMonthNoOverflow(), 2, 'pengunjung-a');

        $stats = $this->stats(new VisitorOverview);

        $this->assertSame('Pengunjung Unik', $stats[0]->getLabel());
        $this->assertSame('2', $stats[0]->getValue());
        $this->assertSame('100,0% dari bulan lalu', $stats[0]->getDescription());

        $this->assertSame('Total Halaman Dilihat', $stats[1]->getLabel());
        $this->assertSame('4', $stats[1]->getValue());
        $this->assertSame('100,0% dari bulan lalu', $stats[1]->getDescription());

        $this->assertSame('Rata-rata Waktu Akses', $stats[2]->getLabel());
        $this->assertSame('1 mnt 30 dtk', $stats[2]->getValue());
        // Bulan lalu tidak ada laporan lama kunjungan, sehingga tidak ada
        // persentase pembanding yang bermakna.
        $this->assertSame('Belum ada laporan lama kunjungan', $stats[2]->getDescription());

        // Kenaikan ditandai ikon panah naik dan warna hijau (success).
        $this->assertSame(Heroicon::OutlinedArrowTrendingUp, $stats[0]->getDescriptionIcon());
        $this->assertSame('success', $stats[0]->getDescriptionColor());
        $this->assertNull($stats[2]->getDescriptionIcon());
        $this->assertSame('gray', $stats[2]->getDescriptionColor());
    }

    /**
     * Penurunan kunjungan ditandai ikon panah turun dan warna merah (danger).
     */
    public function test_penurunan_kunjungan_ditandai_warna_merah(): void
    {
        $this->kunjungan(now(), 2, 'pengunjung-a');
        $this->kunjungan(now()->subMonthNoOverflow(), 4, 'pengunjung-a');
        $this->kunjungan(now()->subMonthNoOverflow(), 1, 'pengunjung-b');

        $stats = $this->stats(new VisitorOverview);

        $this->assertSame('50,0% dari bulan lalu', $stats[0]->getDescription());
        $this->assertSame(Heroicon::OutlinedArrowTrendingDown, $stats[0]->getDescriptionIcon());
        $this->assertSame('danger', $stats[0]->getDescriptionColor());
    }

    /**
     * Kartu "Ringkasan Organisasi" menghitung anggota, wilayah/cabang, berita
     * yang terbit bulan ini, dan kegiatan.
     */
    public function test_kartu_organisasi_menghitung_data_nyata(): void
    {
        $this->anggota('Siti Maimuna', 'active', now());
        $this->anggota('Rina Latuconsina', 'active', now());
        $this->anggota('Martha Pattikawa', 'pending', now()->subMonthNoOverflow());

        Region::query()->create(['name' => 'Maluku', 'type' => 'province', 'is_active' => true]);
        Branch::query()->create(['name' => 'Ranting Ambon', 'is_active' => true]);

        News::query()->create([
            'title' => 'Berita Uji',
            'slug' => 'berita-uji',
            'type' => 'berita',
            'category' => 'organisasi',
            'published_at' => now(),
        ]);
        Activity::query()->create(['title' => 'Kegiatan Uji', 'slug' => 'kegiatan-uji']);
        Event::query()->create(['title' => 'Agenda Uji', 'slug' => 'agenda-uji', 'status' => 'ongoing']);

        $stats = $this->stats(new OrganizationOverview);

        $this->assertSame('Total Anggota', $stats[0]->getLabel());
        $this->assertSame('3', $stats[0]->getValue());
        $this->assertSame('100,0% bulan ini', $stats[0]->getDescription());

        $this->assertSame('Wilayah / Cabang', $stats[1]->getLabel());
        $this->assertSame('2', $stats[1]->getValue());
        $this->assertSame('1 cabang baru bulan ini', $stats[1]->getDescription());

        $this->assertSame('Berita Diterbitkan', $stats[2]->getLabel());
        $this->assertSame('1', $stats[2]->getValue());
        $this->assertSame('Bulan '.tanggal_id(now(), 'F Y'), $stats[2]->getDescription());

        $this->assertSame('Total Kegiatan', $stats[3]->getLabel());
        $this->assertSame('2', $stats[3]->getValue());
        $this->assertSame('1 sedang berlangsung', $stats[3]->getDescription());
    }

    /**
     * Grafik "Trafik Pengunjung Harian" mengikuti pilihan "Bulan ini" /
     * "Bulan lalu" dan menjumlahkan halaman yang dilihat per hari.
     */
    public function test_grafik_trafik_harian_mengikuti_pilihan_bulan(): void
    {
        $this->kunjungan(now(), 2, 'pengunjung-a');
        $this->kunjungan(now()->subMonthNoOverflow(), 5, 'pengunjung-b');

        $bulanIni = new DailyVisitorChart;
        $dataBulanIni = $this->chartData($bulanIni);

        $this->assertSame('Halaman Dilihat', $dataBulanIni['datasets'][0]['label']);
        // Bulan berjalan: labelnya sepanjang hari yang sudah dijalani.
        $this->assertCount((int) now()->format('j'), $dataBulanIni['labels']);
        $this->assertSame(2, array_sum($dataBulanIni['datasets'][0]['data']));
        $this->assertStringContainsString('Data bulan berjalan', (string) $bulanIni->getDescription());

        $bulanLalu = new DailyVisitorChart;
        $bulanLalu->filter = 'bulan-lalu';
        $dataBulanLalu = $this->chartData($bulanLalu);

        $this->assertCount(now()->subMonthNoOverflow()->daysInMonth, $dataBulanLalu['labels']);
        $this->assertSame(5, array_sum($dataBulanLalu['datasets'][0]['data']));
        $this->assertStringContainsString('Data bulan lalu', (string) $bulanLalu->getDescription());

        // Penyaring pada kartu grafik tersedia dalam bahasa Indonesia.
        Livewire::actingAs($this->admin())->test(DailyVisitorChart::class)
            ->assertSet('filter', 'bulan-ini')
            ->assertSee('Bulan ini')
            ->assertSee('Bulan lalu');
    }

    /**
     * Daftar "Pendaftaran Anggota Terbaru" menampilkan lima pendaftar terakhir
     * beserta domisili dan statusnya.
     */
    public function test_daftar_anggota_terbaru_menampilkan_lima_pendaftar_terakhir(): void
    {
        foreach (range(1, 6) as $nomor) {
            $this->anggota('Anggota '.$nomor, 'active', now()->subDays($nomor));
        }

        $this->anggota('Martha Pattikawa', 'pending', now());

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Martha Pattikawa', false)
            ->assertSee('Menunggu', false)
            ->assertSee('Maluku Utara', false)
            ->assertSee('Anggota 1', false)
            ->assertSee('Anggota 4', false)
            // Pendaftar ke-5 dan ke-6 sudah tidak masuk daftar lima terbaru.
            ->assertDontSee('Anggota 5', false)
            ->assertDontSee('Anggota 6', false);
    }

    /**
     * Grafik "Pertumbuhan Anggota" memakai enam bulan terakhir dan tetap
     * menampilkan bulan tanpa pendaftaran sebagai nol.
     */
    public function test_grafik_pertumbuhan_anggota_memakai_enam_bulan_terakhir(): void
    {
        $this->anggota('Grace Pattiasina', 'active', now());
        $this->anggota('Debora Latuconsina', 'active', now()->subMonthsNoOverflow(2)->startOfMonth()->addDays(3));

        $data = $this->chartData(new MemberGrowthChart);

        $this->assertSame('Anggota Baru', $data['datasets'][0]['label']);
        $this->assertCount(6, $data['labels']);
        // Bulan berjalan dan dua bulan lalu masing-masing berisi satu anggota baru.
        $this->assertSame([0, 0, 0, 1, 0, 1], $data['datasets'][0]['data']);
        $this->assertSame(tanggal_id(now()->subMonthsNoOverflow(5), 'M'), $data['labels'][0]);
        $this->assertSame(tanggal_id(now(), 'M'), $data['labels'][5]);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function anggota(string $nama, string $status, mixed $terdaftar): Member
    {
        $anggota = new Member([
            'full_name' => $nama,
            'domicile_region' => 'Maluku Utara',
            'status' => $status,
        ]);

        // Kolom waktu tidak dapat diisi massal, sehingga tanggal daftar diatur
        // langsung agar urutan pendaftar dapat diuji.
        $anggota->created_at = $terdaftar;
        $anggota->save();

        return $anggota;
    }

    private function kunjungan(mixed $waktu, int $jumlah, string $pengunjung, ?int $durasi = null): void
    {
        for ($i = 0; $i < $jumlah; $i++) {
            PageView::query()->create([
                'path' => '/',
                'route_name' => 'home',
                'visitor_hash' => hash('sha256', $pengunjung),
                'session_id' => 'sesi-'.$pengunjung,
                'viewed_at' => $waktu,
                'duration_seconds' => $durasi,
            ]);
        }
    }

    /**
     * @return array<int, Stat>
     */
    private function stats(StatsOverviewWidget $widget): array
    {
        $method = new ReflectionMethod($widget, 'getStats');
        $method->setAccessible(true);

        return $method->invoke($widget);
    }

    /**
     * @return array<string, mixed>
     */
    private function chartData(ChartWidget $widget): array
    {
        $method = new ReflectionMethod($widget, 'getData');
        $method->setAccessible(true);

        return $method->invoke($widget);
    }
}
