<?php

use App\Http\Middleware\RecordPageView;
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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
