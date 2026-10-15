<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Sidik jari pengunjung untuk statistik kunjungan portal publik.
 *
 * Dibuat dari alamat IP dan user agent, lalu di-hash SHA-256, sehingga
 * pengunjung unik dapat dihitung tanpa menyimpan alamat IP maupun data
 * pribadi pengunjung (lihat App\Http\Middleware\RecordPageView).
 */
final class VisitorFingerprint
{
    public static function of(Request $request): string
    {
        return hash('sha256', $request->ip().'|'.(string) $request->userAgent());
    }
}
