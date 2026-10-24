<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Perangkap bot untuk formulir publik.
 *
 * Formulir menyertakan satu kolom yang tidak terlihat dan tidak dapat
 * difokuskan (lihat resources/views/partials/honeypot.blade.php). Pengunjung
 * sungguhan tidak pernah mengisinya, sedangkan bot yang mengisi seluruh kolom
 * akan tertangkap. Penanganannya senyap: pengirim mendapat balasan sukses yang
 * sama, tetapi tidak ada baris yang disimpan — sehingga bot tidak belajar
 * bagaimana menghindari perangkap ini.
 */
final class Honeypot
{
    /**
     * Nama kolom. Dipilih agar tampak seperti kolom "website" yang lazim diisi
     * bot pengirim formulir.
     */
    public const FIELD = 'website';

    public static function triggered(Request $request): bool
    {
        return filled($request->input(self::FIELD));
    }
}
