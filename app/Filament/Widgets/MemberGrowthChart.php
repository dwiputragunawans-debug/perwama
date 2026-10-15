<?php

namespace App\Filament\Widgets;

use App\Support\OrganizationStatistics;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Grafik "Pertumbuhan Anggota" pada dasbor panel.
 *
 * Menampilkan jumlah anggota baru pada enam bulan terakhir, berdampingan dengan
 * daftar "Pendaftaran Anggota Terbaru" (lihat App\Filament\Pages\Dashboard).
 */
class MemberGrowthChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 5;

    /**
     * Satu dari tiga kolom dasbor, sebagai pasangan daftar anggota terbaru
     * yang memakai dua kolom.
     */
    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Pertumbuhan Anggota';

    protected ?string $description = 'Enam bulan terakhir';

    protected ?string $maxHeight = '22rem';

    protected string $color = 'primary';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $perBulan = OrganizationStatistics::newMembersPerMonth(6);

        return [
            'datasets' => [
                [
                    'label' => 'Anggota Baru',
                    'data' => array_values($perBulan),
                ],
            ],
            'labels' => array_map(
                fn (string $bulan): string => tanggal_id(Carbon::createFromFormat('Y-m', $bulan), 'M'),
                array_keys($perBulan),
            ),
        ];
    }

    protected function getOptions(): array|RawJs|null
    {
        return [
            // Keterangan seri tidak perlu karena hanya ada satu seri data.
            'plugins' => ['legend' => ['display' => false]],
            'borderRadius' => 6,
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
