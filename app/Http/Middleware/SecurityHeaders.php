<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan dasar untuk setiap respons aplikasi.
 *
 * Header ini sebelumnya hanya bisa muncul bila diatur pada web server/CDN,
 * sehingga hilang begitu aplikasi dipindahkan ke server lain. Nilai di bawah
 * dipilih agar tidak mengganggu panel Filament (Alpine/Livewire memakai skrip
 * dan gaya sebaris, serta unggahan berkas pada origin yang sama), tetapi tetap
 * menutup vektor umum: sniffing jenis konten, pembingkaian halaman (clickjacking),
 * kebocoran URL lewat Referer, dan akses perangkat dari halaman.
 *
 * HSTS tidak ditetapkan di sini karena bergantung pada sertifikat dan domain
 * (termasuk subdomain) yang hanya diketahui saat deployment.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        /*
         * Content-Security-Policy sengaja hanya mengatur direktif yang tidak
         * memengaruhi skrip/gaya: frame-ancestors (clickjacking, cadangan
         * X-Frame-Options), base-uri, object-src, dan form-action. Tanpa
         * default-src, seluruh sumber daya lain tetap seperti biasa sehingga
         * editor kaya dan pemutar video tidak rusak.
         */
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Content-Security-Policy' => "frame-ancestors 'self'; base-uri 'self'; object-src 'none'; form-action 'self'",
        ];

        foreach ($headers as $name => $value) {
            // Header dari web server/CDN tidak ditimpa.
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
