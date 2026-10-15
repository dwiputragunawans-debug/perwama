<?php

namespace App\Support;

use App\Models\PageView;
use Carbon\CarbonInterface;

/**
 * Perhitungan statistik kunjungan portal publik.
 *
 * Dipakai oleh bagian "Ringkasan Pengunjung Website" pada dasbor panel admin.
 * Semua metode menerima rentang tanggal eksplisit supaya bagian yang menampilkan
 * (widget) tetap tipis dan perhitungannya dapat diuji terpisah.
 */
final class VisitorStatistics
{
    /**
     * Ringkasan kunjungan pada satu rentang waktu.
     *
     * `average_seconds` bernilai null bila belum ada satu pun laporan lama
     * kunjungan, sehingga tampilan dapat membedakan "belum ada data" dari
     * kunjungan yang berlangsung sangat singkat.
     *
     * @return array{unique_visitors: int, page_views: int, average_seconds: float|null}
     */
    public static function summary(CarbonInterface $start, CarbonInterface $end): array
    {
        $baris = PageView::query()
            ->between($start, $end)
            ->selectRaw('count(*) as total')
            ->selectRaw('count(distinct visitor_hash) as unik')
            ->selectRaw('avg(duration_seconds) as rata_rata')
            ->first();

        return [
            'unique_visitors' => (int) ($baris->unik ?? 0),
            'page_views' => (int) ($baris->total ?? 0),
            'average_seconds' => filled($baris->rata_rata ?? null) ? (float) $baris->rata_rata : null,
        ];
    }

    /**
     * Jumlah halaman dilihat per hari pada satu rentang waktu.
     *
     * Dikelompokkan dengan fungsi `date()` agar berjalan sama pada MySQL
     * maupun SQLite (basis data pengujian).
     *
     * @return array<string, int> kunci berupa tanggal `Y-m-d`
     */
    public static function dailyPageViews(CarbonInterface $start, CarbonInterface $end): array
    {
        return PageView::query()
            ->between($start, $end)
            ->selectRaw('date(viewed_at) as hari')
            ->selectRaw('count(*) as jumlah')
            ->groupBy('hari')
            ->pluck('jumlah', 'hari')
            ->map(fn (mixed $jumlah): int => (int) $jumlah)
            ->all();
    }
}
