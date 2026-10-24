<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proxy / CDN Terpercaya (Rate Limit per Alamat IP)
    |--------------------------------------------------------------------------
    |
    | Isi dengan alamat proxy/CDN (dipisah koma), atau `*` bila pengunjung
    | tidak dapat menjangkau server secara langsung (mis. Cloudflare). Nilai
    | ini membuat `$request->ip()` memakai X-Forwarded-For, sehingga batas
    | laju formulir publik dihitung per pengunjung, bukan per proxy.
    |
    | Dibaca lewat konfigurasi (bukan langsung di bootstrap/app.php) agar
    | tetap bekerja ketika `php artisan config:cache` dipakai di produksi.
    | Kosong = header X-Forwarded-For tidak dipercaya (aman dari pemalsuan).
    |
    */

    'trusted_proxies' => env('TRUSTED_PROXIES'),

    /*
    |--------------------------------------------------------------------------
    | Retensi Data Pribadi Pendaftar
    |--------------------------------------------------------------------------
    |
    | NIK disimpan terenkripsi, dan data pribadi pendaftaran yang DITOLAK
    | dianonimkan otomatis oleh `php artisan membership:purge-rejected`
    | (dijadwalkan harian) setelah melewati jumlah hari ini. Sesuaikan dengan
    | kebijakan organisasi dan kewajiban perlindungan data yang berlaku.
    |
    */

    'membership_retention_days' => (int) env('MEMBERSHIP_RETENTION_DAYS', 180),

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
    | seeder tidak menimpa kata sandi yang sudah diganti pengelola. Seluruh
    | nilai dapat ditimpa lewat variabel ADMIN_* pada berkas .env.
    |
    | PENTING: tidak ada kata sandi bawaan. `password` sengaja tidak dipakai
    | sebagai nilai cadangan karena akun dengan kredensial yang diketahui
    | publik membuat panel admin dapat diambil alih. Seeder menolak berjalan
    | (lihat AdminUserSeeder) bila ADMIN_PASSWORD kosong atau terlalu lemah
    | pada environment selain local/testing.
    |
    */

    'admins' => [
        [
            'name' => env('ADMIN_NAME', 'Administrator PERWAMA INA'),
            'email' => env('ADMIN_EMAIL', 'admin@perwama-ina.or.id'),
            // Wajib diisi lewat ADMIN_PASSWORD (minimal 12 karakter).
            'password' => env('ADMIN_PASSWORD'),
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
         *     'password' => env('ADMIN_PASSWORD_KEDUA'),
         *     'phone' => '0813-0000-0000',
         * ],
         */
    ],

];
