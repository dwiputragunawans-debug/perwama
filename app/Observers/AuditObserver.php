<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

/**
 * Mencatat perubahan data penting ke tabel `audit_logs`.
 *
 * Sebelumnya resource Log Aktivitas hanya berisi formulir kosong: tidak ada
 * kode yang menulis log, sehingga catatan aktivitas admin tidak pernah ada.
 * Observer ini mengisi tabel tersebut setiap kali pengguna yang login membuat,
 * mengubah, atau menghapus data — termasuk perubahan dari panel Filament dan
 * dari seeder yang dijalankan manual oleh admin.
 *
 * Perubahan tanpa sesi login (pengunjung publik, seeder otomatis) tidak
 * dicatat agar log tetap berisi tindakan pengelola, bukan lalu lintas situs.
 * Kata sandi dan token tidak pernah disimpan.
 */
class AuditObserver
{
    /**
     * Kolom yang tidak boleh masuk log: kredensial serta data pribadi yang
     * paling sensitif tidak diduplikasi ke tabel log (lihat temuan SEC-04).
     */
    private const DIKECUALIKAN = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
        'nik', 'address', 'birth_date', 'birth_place',
    ];

    /**
     * Stempel waktu tidak perlu dicatat; perubahan yang hanya menyentuh kolom
     * ini dianggap bukan perubahan data.
     */
    private const STEMPEL_WAKTU = ['created_at', 'updated_at'];

    public function created(Model $model): void
    {
        $this->catat('created', $model, [], $this->saring($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $perubahan = Arr::except($model->getChanges(), array_merge(self::DIKECUALIKAN, self::STEMPEL_WAKTU));

        if ($perubahan === []) {
            return;
        }

        $lama = [];

        foreach (array_keys($perubahan) as $kolom) {
            $lama[$kolom] = $model->getOriginal($kolom);
        }

        $this->catat('updated', $model, $this->saring($lama), $this->saring($perubahan));
    }

    public function deleted(Model $model): void
    {
        $this->catat('deleted', $model, $this->saring($model->getOriginal()), []);
    }

    /**
     * @param  array<string, mixed>  $lama
     * @param  array<string, mixed>  $baru
     */
    private function catat(string $aksi, Model $model, array $lama, array $baru): void
    {
        if (! Auth::check()) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => Auth::id(),
            'action' => $aksi,
            'model_type' => $model::class,
            'model_id' => $model->getKey(),
            'old_values' => $lama,
            'new_values' => $baru,
            'ip_address' => Request::ip(),
            'user_agent' => Str::limit((string) Request::userAgent(), 250, ''),
        ]);
    }

    /**
     * Ubah nilai model menjadi data yang aman disimpan: kolom sensitif dibuang,
     * teks panjang dipotong, dan tanggal diformat agar mudah dibaca.
     *
     * @param  array<string, mixed>  $nilai
     * @return array<string, mixed>
     */
    private function saring(array $nilai): array
    {
        $hasil = [];

        foreach (Arr::except($nilai, array_merge(self::DIKECUALIKAN, self::STEMPEL_WAKTU)) as $kolom => $isi) {
            if ($isi instanceof \DateTimeInterface) {
                $isi = $isi->format('Y-m-d H:i:s');
            } elseif (is_array($isi) || is_object($isi)) {
                $isi = json_encode($isi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $hasil[$kolom] = is_string($isi) ? Str::limit($isi, 500) : $isi;
        }

        return $hasil;
    }
}
