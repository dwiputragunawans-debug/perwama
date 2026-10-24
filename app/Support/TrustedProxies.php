<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Proxy/CDN yang dipercaya saat menentukan alamat IP pengunjung.
 *
 * Pembatasan laju formulir publik dihitung per alamat IP. Bila situs berada di
 * belakang proxy/CDN, `$request->ip()` akan selalu berisi alamat proxy tanpa
 * konfigurasi ini — akibatnya semua pengunjung berbagi satu kuota. Sebaliknya,
 * mempercayai header `X-Forwarded-For` dari klien yang dapat menjangkau server
 * langsung membuat batas laju mudah dilewati. Karena itu nilainya eksplisit:
 * kosong berarti tidak ada proxy yang dipercaya.
 *
 * Nilai dibaca dari config (`perwama.trusted_proxies`) dan diterapkan pada
 * AppServiceProvider::boot(), bukan dari `bootstrap/app.php`, karena berkas itu
 * berjalan sebelum .env dimuat dan sebelum konfigurasi tersedia — sehingga
 * `env()` di sana bernilai null ketika konfigurasi di-cache di produksi.
 */
final class TrustedProxies
{
    /**
     * Header yang dipakai untuk menebak protokol/host/IP asli, sama dengan
     * bawaan middleware TrustProxies Laravel.
     */
    private const HEADER = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_PREFIX
        | Request::HEADER_X_FORWARDED_AWS_ELB;

    public static function apply(?string $daftar): void
    {
        Request::setTrustedProxies(self::parse($daftar), self::HEADER);
    }

    /**
     * `*` (semua proxy) atau daftar alamat dipisah koma.
     *
     * @return list<string>
     */
    public static function parse(?string $daftar): array
    {
        $daftar = trim((string) $daftar);

        if ($daftar === '') {
            return [];
        }

        if ($daftar === '*') {
            return ['*'];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $daftar)),
            fn (string $alamat): bool => $alamat !== '',
        ));
    }
}
