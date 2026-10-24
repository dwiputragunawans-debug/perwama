<?php

use App\Http\Middleware\ConfigureTrustedProxies;
use App\Http\Middleware\RecordPageView;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Setiap tampilan halaman publik dicatat sebagai kunjungan
         * (tabel `page_views`). Datanya menjadi angka pada bagian "Ringkasan
         * Pengunjung Website" di dasbor panel admin.
         */
        $middleware->web(append: [
            RecordPageView::class,
        ]);

        /*
         * Header keamanan (nosniff, anti-clickjacking, referrer, permissions)
         * dipasang untuk semua respons, termasuk halaman galat dan panel,
         * sehingga tidak bergantung pada konfigurasi web server.
         *
         * ConfigureTrustedProxies diletakkan di sini (tumpukan global, setelah
         * middleware bawaan TrustProxies) agar daftar proxy dari konfigurasi
         * benar-benar dipakai saat menghitung alamat IP pengunjung.
         */
        $middleware->append([
            ConfigureTrustedProxies::class,
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
