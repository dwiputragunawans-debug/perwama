<?php

namespace App\Filament\Widgets;

use App\Support\VisitorStatistics;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

/**
 * Grafik "Trafik Pengunjung Harian" pada dasbor panel.
 *
 * Menampilkan jumlah halaman yang dilihat pengunjung setiap hari pada bulan
 * berjalan (sampai hari ini) atau bulan lalu, sesuai pilihan pada tombol
 * "Bulan ini" / "Bulan lalu" di pojok kartu.
 */
class DailyVisitorChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Trafik Pengunjung Harian';

    /**
     * Warna garis mengikuti warna brand ketiga panel (terracotta), lihat
     * daftar warna pada AdminPanelProvider.
     */
    protected string $color = 'danger';

    public ?string $filter = 'bulan-ini';

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            'bulan-ini' => 'Bulan ini',
            'bulan-lalu' => 'Bulan lalu',
        ];
    }

    public function getDescription(): string|Htmlable|null
    {
        [$mulai, $akhir] = $this->range();

        $judul = $this->filter === 'bulan-lalu' ? 'Data bulan lalu' : 'Data bulan berjalan';

        return $judul.' ('.rentang_tanggal_id($mulai, $akhir).')';
    }

    protected function getData(): array
    {
        [$mulai, $akhir] = $this->range();

        $perHari = VisitorStatistics::dailyPageViews($mulai, $akhir);

        $label = [];
        $jumlah = [];

        for ($hari = $mulai->copy(); $hari <= $akhir; $hari->addDay()) {
            $label[] = tanggal_id($hari, 'j M');
            $jumlah[] = $perHari[$hari->format('Y-m-d')] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Halaman Dilihat',
                    'data' => $jumlah,
                ],
            ],
            'labels' => $label,
        ];
    }

    protected function getOptions(): array|RawJs|null
    {
        return [
            // Keterangan seri tidak perlu karena hanya ada satu seri data.
            'plugins' => ['legend' => ['display' => false]],
            // Area di bawah garis diisi tipis agar tren mudah dibaca.
            'elements' => [
                'line' => ['tension' => 0.35, 'fill' => true],
                'point' => ['radius' => 4],
            ],
            'pointBackgroundColor' => '#ffffff',
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }

    /**
     * Rentang tanggal sesuai pilihan penyaring.
     *
     * Bulan berjalan dibatasi sampai hari ini agar grafik tidak menurun ke nol
     * pada hari-hari yang belum dijalani.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(): array
    {
        [$mulai, $akhir] = rentang_bulan($this->filter === 'bulan-lalu' ? now()->subMonthNoOverflow() : null);

        if ($this->filter === 'bulan-lalu') {
            return [$mulai, $akhir];
        }

        return [$mulai, now()->lessThan($akhir) ? now() : $akhir];
    }
}
