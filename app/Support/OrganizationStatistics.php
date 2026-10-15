<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Branch;
use App\Models\Event;
use App\Models\Member;
use App\Models\News;
use App\Models\Region;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Perhitungan ringkasan organisasi untuk dasbor panel admin.
 *
 * Bagian "Ringkasan Organisasi" memantau pertumbuhan anggota sekaligus
 * aktivitas terbaru: jumlah anggota, wilayah/cabang, berita yang diterbitkan,
 * dan kegiatan yang tercatat. Seluruh perhitungan dikumpulkan di sini agar
 * widget tampilan tetap tipis dan angkanya dapat diuji terpisah.
 *
 * Tanggal pendaftaran anggota memakai `created_at` (saat data masuk ke sistem),
 * bukan `joined_at` yang bersifat opsional dan dapat berisi tanggal lampau.
 */
final class OrganizationStatistics
{
    /** Total anggota yang tercatat, termasuk yang masih menunggu verifikasi. */
    public static function memberTotal(): int
    {
        return Member::query()->count();
    }

    /** Anggota baru yang datanya masuk pada rentang waktu tertentu. */
    public static function newMembers(CarbonInterface $start, CarbonInterface $end): int
    {
        return Member::query()->whereBetween('created_at', [$start, $end])->count();
    }

    /** Jumlah wilayah aktif ditambah cabang aktif. */
    public static function unitTotal(): int
    {
        return Region::query()->where('is_active', true)->count()
            + Branch::query()->where('is_active', true)->count();
    }

    /** Cabang yang baru dibuat pada rentang waktu tertentu. */
    public static function newBranches(CarbonInterface $start, CarbonInterface $end): int
    {
        return Branch::query()->whereBetween('created_at', [$start, $end])->count();
    }

    /**
     * Berita (dan artikel) yang sudah terbit pada rentang waktu tertentu.
     * Terbitan yang tanggalnya masih di depan tidak ikut dihitung.
     */
    public static function publishedNews(CarbonInterface $start, CarbonInterface $end): int
    {
        return News::query()
            ->published()
            ->whereBetween('published_at', [$start, $end])
            ->count();
    }

    /** Total kegiatan, yaitu kegiatan lapangan ditambah agenda/acara. */
    public static function activityTotal(): int
    {
        return Activity::query()->count() + Event::query()->count();
    }

    /** Agenda yang sedang berlangsung saat ini. */
    public static function ongoingEvents(): int
    {
        return Event::query()->where('status', 'ongoing')->count();
    }

    /**
     * Anggota baru per bulan selama beberapa bulan terakhir.
     *
     * Bulan tanpa pendaftaran tetap dikembalikan dengan nilai 0 agar grafik
     * pertumbuhan anggota tidak berlubang.
     *
     * @return array<string, int> kunci berupa bulan `Y-m`
     */
    public static function newMembersPerMonth(int $months = 6, ?CarbonInterface $until = null): array
    {
        $akhir = $until ? Carbon::parse($until)->endOfMonth() : Carbon::now()->endOfMonth();
        $mulai = $akhir->copy()->subMonthsNoOverflow($months - 1)->startOfMonth();

        $perHari = Member::query()
            ->whereBetween('created_at', [$mulai, $akhir])
            ->selectRaw('date(created_at) as hari')
            ->selectRaw('count(*) as jumlah')
            ->groupBy('hari')
            ->pluck('jumlah', 'hari');

        $perBulan = [];

        for ($bulan = $mulai->copy(); $bulan <= $akhir; $bulan->addMonthNoOverflow()) {
            $perBulan[$bulan->format('Y-m')] = 0;
        }

        foreach ($perHari as $hari => $jumlah) {
            $kunci = Carbon::parse($hari)->format('Y-m');

            if (array_key_exists($kunci, $perBulan)) {
                $perBulan[$kunci] += (int) $jumlah;
            }
        }

        return $perBulan;
    }
}
