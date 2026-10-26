<?php

namespace App\Console\Commands;

use App\Models\MembershipApplication;
use Illuminate\Console\Command;

/**
 * Menerapkan kebijakan retensi data pribadi (temuan SEC-04).
 *
 * Pendaftar yang ditolak tidak boleh disimpan beserta NIK, alamat, dan kontak
 * selamanya. Setelah masa retensi berakhir, data pribadinya dianonimkan —
 * baris tetap ada agar statistik jumlah pendaftaran dan status peninjauan
 * masih dapat dipertanggungjawabkan.
 */
class PurgeRejectedMembershipData extends Command
{
    protected $signature = 'membership:purge-rejected
                            {--days= : Masa retensi dalam hari (bawaan: config perwama.membership_retention_days)}';

    protected $description = 'Anonimkan data pribadi pendaftar yang ditolak setelah masa retensi berakhir';

    public function handle(): int
    {
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : (int) config('perwama.membership_retention_days', 180);

        if ($days < 1) {
            $this->error('Masa retensi harus minimal 1 hari.');

            return self::FAILURE;
        }

        $batas = now()->subDays($days);
        $jumlah = 0;

        MembershipApplication::query()
            ->withTrashed()
            ->where('status', 'rejected')
            ->whereRaw('COALESCE(reviewed_at, updated_at) <= ?', [$batas->toDateTimeString()])
            ->chunkById(100, function ($aplikasi) use (&$jumlah): void {
                foreach ($aplikasi as $pendaftaran) {
                    $pendaftaran->forceFill([
                        'full_name' => 'Pendaftar Ditolak (dianonimkan)',
                        'nik' => null,
                        'birth_place' => null,
                        'birth_date' => null,
                        'address' => null,
                        'city' => null,
                        'province' => null,
                        'whatsapp' => null,
                        'email' => null,
                        'education' => null,
                        'occupation' => null,
                        'interest_field' => null,
                        'maluku_origin' => null,
                        'domicile_region' => null,
                        'photo' => null,
                        'motivation' => null,
                        'admin_notes' => null,
                    ])->save();

                    $jumlah++;
                }
            });

        $this->info("{$jumlah} pendaftaran ditolak dianonimkan (retensi {$days} hari, sebelum {$batas->format('Y-m-d')}).");

        return self::SUCCESS;
    }
}
