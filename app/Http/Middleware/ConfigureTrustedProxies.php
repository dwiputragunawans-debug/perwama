<?php

namespace App\Http\Middleware;

use App\Support\TrustedProxies;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menerapkan proxy/CDN terpercaya dari konfigurasi.
 *
 * Laravel menaruh middleware bawaan `TrustProxies` pada tumpukan global dan
 * mengosongkan daftar proxy di awal setiap permintaan. Middleware ini ditambahkan
 * **setelah** tumpukan tersebut (lihat bootstrap/app.php), sehingga nilai dari
 * `config('perwama.trusted_proxies')` benar-benar dipakai saat
 * `$request->ip()` dihitung — termasuk oleh pembatasan laju formulir publik.
 *
 * Nilai dibaca dari konfigurasi, bukan `env()`, agar tetap berlaku ketika
 * `php artisan config:cache` dipakai di produksi.
 */
class ConfigureTrustedProxies
{
    public function handle(Request $request, Closure $next): Response
    {
        TrustedProxies::apply(config('perwama.trusted_proxies'));

        return $next($request);
    }
}
