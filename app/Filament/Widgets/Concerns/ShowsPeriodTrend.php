<?php

namespace App\Filament\Widgets\Concerns;

use Filament\Support\Icons\Heroicon;

/**
 * Perubahan periode (naik/turun) untuk keterangan pada kartu statistik dasbor.
 *
 * Dipakai bersama oleh kartu "Ringkasan Pengunjung Website" dan "Ringkasan
 * Organisasi" agar teks, ikon, serta warnanya seragam: naik berwarna hijau
 * (`success`), turun berwarna merah (`danger`), dan tanpa data pembanding
 * dibiarkan abu-abu tanpa ikon.
 */
trait ShowsPeriodTrend
{
    /**
     * Perubahan dalam persen dibanding periode sebelumnya.
     *
     * Mengembalikan null bila periode sebelumnya belum memiliki data, karena
     * persentase dari nol tidak bermakna (bukan berarti naik tak terhingga).
     */
    protected function trendPercentage(float|int|null $current, float|int|null $previous): ?float
    {
        if (blank($previous) || $previous <= 0) {
            return null;
        }

        return (((float) $current - (float) $previous) / (float) $previous) * 100;
    }

    protected function trendDescription(
        ?float $percentage,
        string $comparison = 'dari bulan lalu',
        string $fallback = 'Belum ada data bulan lalu',
    ): string {
        if ($percentage === null) {
            return $fallback;
        }

        return persen_id(abs($percentage)).' '.$comparison;
    }

    protected function trendIcon(?float $percentage): ?Heroicon
    {
        return match (true) {
            $percentage === null, $percentage === 0.0 => null,
            $percentage > 0 => Heroicon::OutlinedArrowTrendingUp,
            default => Heroicon::OutlinedArrowTrendingDown,
        };
    }

    protected function trendColor(?float $percentage): string
    {
        return match (true) {
            $percentage === null, $percentage === 0.0 => 'gray',
            $percentage > 0 => 'success',
            default => 'danger',
        };
    }
}
