<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ShowsPeriodTrend;
use App\Support\OrganizationStatistics;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Kartu "Ringkasan Organisasi" pada dasbor panel.
 *
 * Menampilkan perkembangan anggota, wilayah/cabang, berita yang diterbitkan
 * bulan ini, dan jumlah kegiatan yang tercatat. Perhitungannya ada pada
 * App\Support\OrganizationStatistics.
 */
class OrganizationOverview extends StatsOverviewWidget
{
    use ShowsPeriodTrend;

    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Ringkasan Organisasi';

    protected ?string $description = 'Pantau perkembangan anggota dan aktivitas terbaru.';

    protected function getStats(): array
    {
        [$mulai, $akhir] = rentang_bulan();
        [$mulaiLalu, $akhirLalu] = rentang_bulan(now()->subMonthNoOverflow());

        $anggotaBaru = OrganizationStatistics::newMembers($mulai, $akhir);
        $anggotaBaruLalu = OrganizationStatistics::newMembers($mulaiLalu, $akhirLalu);
        $persenAnggota = $this->trendPercentage($anggotaBaru, $anggotaBaruLalu);

        $cabangBaru = OrganizationStatistics::newBranches($mulai, $akhir);
        $kegiatanBerlangsung = OrganizationStatistics::ongoingEvents();

        return [
            Stat::make('Total Anggota', angka_id(OrganizationStatistics::memberTotal()))
                ->icon(Heroicon::OutlinedUserGroup)
                ->color('primary')
                ->description($this->trendDescription(
                    $persenAnggota,
                    comparison: 'bulan ini',
                    fallback: $anggotaBaru > 0
                        ? angka_id($anggotaBaru).' anggota baru bulan ini'
                        : 'Belum ada anggota baru bulan ini',
                ))
                ->descriptionIcon($this->trendIcon($persenAnggota), IconPosition::Before)
                ->descriptionColor($this->trendColor($persenAnggota)),

            Stat::make('Wilayah / Cabang', angka_id(OrganizationStatistics::unitTotal()))
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('gold')
                ->description($cabangBaru > 0
                    ? angka_id($cabangBaru).' cabang baru bulan ini'
                    : 'Belum ada cabang baru bulan ini')
                ->descriptionIcon($cabangBaru > 0 ? Heroicon::OutlinedArrowTrendingUp : null, IconPosition::Before)
                ->descriptionColor($cabangBaru > 0 ? 'success' : 'gray'),

            Stat::make('Berita Diterbitkan', angka_id(OrganizationStatistics::publishedNews($mulai, $akhir)))
                ->icon(Heroicon::OutlinedNewspaper)
                ->color('success')
                ->description('Bulan '.tanggal_id($mulai, 'F Y'))
                ->descriptionColor('gray'),

            Stat::make('Total Kegiatan', angka_id(OrganizationStatistics::activityTotal()))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('danger')
                ->description($kegiatanBerlangsung > 0
                    ? angka_id($kegiatanBerlangsung).' sedang berlangsung'
                    : 'Tidak ada yang berlangsung')
                ->descriptionColor($kegiatanBerlangsung > 0 ? 'warning' : 'gray'),
        ];
    }
}
