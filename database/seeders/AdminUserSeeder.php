<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Membuat seluruh akun administrator panel Filament.
     *
     * Daftar akun dibaca dari `config/perwama.php` (kunci `admins`), sehingga
     * jumlah admin tidak dibatasi satu akun. Tambahkan entri baru pada daftar
     * tersebut setiap kali ada pengelola website tambahan.
     */
    public function run(): void
    {
        $admins = config('perwama.admins', []);

        if (blank($admins)) {
            $this->command->warn('Tidak ada akun pada config/perwama.php, seeder dilewati.');

            return;
        }

        foreach ($admins as $data) {
            $admin = User::firstOrNew(['email' => $data['email']]);

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
                $admin->password = Hash::make($data['password'] ?? 'password');
            }

            $admin->save();

            $status = $admin->wasRecentlyCreated ? 'dibuat' : 'disinkronkan';

            $this->command->info("Akun admin ({$status}): {$admin->email}");
        }

        $this->command->info('Total admin aktif: '.User::query()->admin()->count());
    }
}
