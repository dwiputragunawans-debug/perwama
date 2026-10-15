<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ShowsPeriodTrend;
use App\Support\VisitorStatistics;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Kartu "Ringkasan Pengunjung Website" pada dasbor panel.
 *
 * Angka berasal dari tabel `page_views` (kunjungan nyata portal publik, lihat
 * App\Http\Middleware\RecordPageView) dan dibandingkan dengan bulan sebelumnya
 * agar pengelola dapat melihat arah perkembangannya.
 *
 * Statistik tidak dimuat secara malas (`$isLazy = false`) supaya kartu langsung
 * tampil tanpa kerangka pemuatan saat dasbor dibuka.
 */
class VisitorOverview extends StatsOverviewWidget
{
    use ShowsPeriodTrend;

    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Ringkasan Pengunjung Website';

    protected ?string $description = 'Statistik trafik dan interaksi pengunjung portal publik bulan ini.';

    protected function getStats(): array
    {
        [$mulai, $akhir] = rentang_bulan();
        [$mulaiLalu, $akhirLalu] = rentang_bulan(now()->subMonthNoOverflow());

        $bulanIni = VisitorStatistics::summary($mulai, $akhir);
        $bulanLalu = VisitorStatistics::summary($mulaiLalu, $akhirLalu);

        return [
            $this->stat(
                label: 'Pengunjung Unik',
                value: angka_id($bulanIni['unique_visitors']),
                icon: Heroicon::OutlinedEye,
                color: 'success',
                current: $bulanIni['unique_visitors'],
                previous: $bulanLalu['unique_visitors'],
            ),
            $this->stat(
                label: 'Total Halaman Dilihat',
                value: angka_id($bulanIni['page_views']),
                icon: Heroicon::OutlinedChartBar,
                color: 'primary',
                current: $bulanIni['page_views'],
                previous: $bulanLalu['page_views'],
            ),
            $this->stat(
                label: 'Rata-rata Waktu Akses',
                value: durasi_id((int) round($bulanIni['average_seconds'] ?? 0)),
                icon: Heroicon::OutlinedClock,
                color: 'danger',
                current: $bulanIni['average_seconds'] ?? 0,
                previous: $bulanLalu['average_seconds'] ?? 0,
                fallback: 'Belum ada laporan lama kunjungan',
            ),
        ];
    }

    /**
     * Satu kartu statistik lengkap dengan keterangan arah perubahan.
     */
    private function stat(
        string $label,
        string $value,
        Heroicon $icon,
        string $color,
        float|int $current,
        float|int $previous,
        string $fallback = 'Belum ada data bulan lalu',
    ): Stat {
        $persen = $this->trendPercentage($current, $previous);

        return Stat::make($label, $value)
            ->icon($icon)
            ->color($color)
            ->description($this->trendDescription($persen, fallback: $fallback))
            ->descriptionIcon($this->trendIcon($persen), IconPosition::Before)
            ->descriptionColor($this->trendColor($persen));
    }
}
