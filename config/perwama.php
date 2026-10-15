<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Akun Administrator Panel Filament
    |--------------------------------------------------------------------------
    |
    | Seeder `AdminUserSeeder` membuat (atau menyelaraskan) setiap akun pada
    | daftar di bawah ini sebagai administrator panel. Jumlah admin TIDAK
    | dibatasi: tambahkan satu entri baru untuk setiap pengelola website,
    | lalu jalankan `php artisan db:seed --class=AdminUserSeeder`.
    |
    | Kata sandi hanya dipakai ketika akun pertama kali dibuat, sehingga
    | seeder tidak menimpa kata sandi yang sudah diganti pengelola. Nilai
    | bawaan dapat ditimpa lewat variabel ADMIN_* pada berkas .env.
    |
    */

    'admins' => [
        [
            'name' => env('ADMIN_NAME', 'Administrator PERWAMA INA'),
            'email' => env('ADMIN_EMAIL', 'admin@perwama-ina.or.id'),
            'password' => env('ADMIN_PASSWORD', 'password'),
            'phone' => env('ADMIN_PHONE', '0812-0000-0000'),
        ],

        /*
         * Contoh admin kedua. Buka komentar (dan ganti datanya) bila ada
         * pengelola website tambahan. Akun juga dapat dibuat langsung dari
         * panel: menu Pengguna → Tambah Pengguna → aktifkan "Akses Admin".
         *
         * [
         *     'name' => 'Pengelola Website',
         *     'email' => 'pengelola@perwama-ina.or.id',
         *     'password' => env('ADMIN_PASSWORD_KEDUA', 'password'),
         *     'phone' => '0813-0000-0000',
         * ],
         */
    ],

];
