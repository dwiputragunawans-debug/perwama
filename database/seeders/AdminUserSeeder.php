<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Kata sandi yang terlalu mudah ditebak, sehingga ditolak pada server
     * sungguhan (lihat `$wajibKuat` di bawah).
     */
    private const KATA_SANDI_LEMAH = [
        'password', 'passw0rd', 'admin', 'administrator', 'admin123',
        '12345678', '123456789', '1234567890', 'qwerty', 'perwama',
        'perwama123', 'rahasia',
    ];

    /**
     * Membuat seluruh akun administrator panel Filament.
     *
     * Daftar akun dibaca dari `config/perwama.php` (kunci `admins`), sehingga
     * jumlah admin tidak dibatasi satu akun. Tambahkan entri baru pada daftar
     * tersebut setiap kali ada pengelola website tambahan.
     *
     * Seeder ini tidak memiliki kata sandi bawaan: pada environment selain
     * `local`/`testing`, ADMIN_PASSWORD wajib diisi dan harus cukup kuat.
     * Dengan begitu, server yang lupa mengatur variabel tersebut gagal
     * menyeed alih-alih membuat akun dengan kredensial yang diketahui publik.
     */
    public function run(): void
    {
        $admins = config('perwama.admins', []);

        if (blank($admins)) {
            $this->command?->warn('Tidak ada akun pada config/perwama.php, seeder dilewati.');

            return;
        }

        /*
         * Di local/testing kata sandi acak masih dapat dibuat agar pengembang
         * tidak terhalang; di server sungguhan seeder harus berhenti.
         */
        $wajibKuat = ! app()->environment(['local', 'testing']);

        foreach ($admins as $data) {
            if (blank($data['email'] ?? null)) {
                throw new RuntimeException('Setiap akun admin pada config/perwama.php harus memiliki email.');
            }

            /*
             * Email tetap unik meski akun sudah di-soft-delete. Sertakan baris
             * terhapus agar seeder tidak mencoba membuat akun kedua dengan email
             * yang sama; akun yang dikonfigurasi lalu dipulihkan.
             */
            $admin = User::withTrashed()->firstOrNew(['email' => $data['email']]);
            $dipulihkan = $admin->trashed();

            $admin->name = $data['name'];

            // Dipastikan selalu punya akses admin, termasuk bila sebelumnya sempat dicabut.
            $admin->is_admin = true;

            if (filled($data['phone'] ?? null)) {
                $admin->phone = $data['phone'];
            }

            /*
             * Kata sandi hanya diisi untuk akun baru, agar kata sandi yang sudah
             * diganti pengelola tidak tertimpa saat seeder dijalankan ulang.
             */
            if (! $admin->exists) {
                $admin->password = Hash::make($this->kataSandi($data, $wajibKuat));
            }

            $admin->save();

            if ($dipulihkan) {
                $admin->restore();
            }

            $status = match (true) {
                $admin->wasRecentlyCreated => 'dibuat',
                $dipulihkan => 'dipulihkan',
                default => 'disinkronkan',
            };

            $this->command?->info("Akun admin ({$status}): {$admin->email}");
        }

        $this->command?->info('Total admin aktif: '.User::query()->admin()->count());
    }

    /**
     * Kata sandi untuk akun baru, atau gagal bila tidak layak dipakai.
     *
     * @param  array<string, mixed>  $data
     */
    private function kataSandi(array $data, bool $wajibKuat): string
    {
        $password = trim((string) ($data['password'] ?? ''));

        if ($wajibKuat && ($password === '' || mb_strlen($password) < 12 || in_array(Str::lower($password), self::KATA_SANDI_LEMAH, true))) {
            throw new RuntimeException(
                'ADMIN_PASSWORD belum diatur atau terlalu lemah untuk akun '.($data['email'] ?? '-').'. '
                .'Isi variabel tersebut dengan kata sandi minimal 12 karakter yang tidak mudah ditebak, '
                .'lalu jalankan ulang `php artisan db:seed --class=AdminUserSeeder`.'
            );
        }

        if ($password !== '') {
            return $password;
        }

        /*
         * local/testing tanpa ADMIN_PASSWORD: kata sandi acak ditampilkan sekali
         * agar pengembang tetap dapat masuk tanpa kata sandi yang mudah ditebak.
         */
        $password = Str::password(20);

        $this->command?->warn(
            "ADMIN_PASSWORD kosong. Kata sandi sementara untuk {$data['email']}: {$password} "
            .'(ganti setelah masuk pertama kali, atau isi ADMIN_PASSWORD pada .env).'
        );

        return $password;
    }
}
