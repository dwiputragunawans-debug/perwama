<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('media_url')) {
    /**
     * URL publik untuk berkas yang tersimpan pada disk `public`.
     */
    function media_url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', '/'])) {
            return $path;
        }

        /*
         * Foto asli PERWAMA yang ikut di repositori (public/images/...) dipakai
         * langsung, sehingga tidak memerlukan symlink storage.
         */
        if (is_file(public_path($path))) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('media_or_placeholder')) {
    /**
     * URL gambar, atau null bila kosong (view menampilkan placeholder sendiri).
     */
    function media_or_placeholder(?string $path): ?string
    {
        return media_url($path);
    }
}

if (! function_exists('whatsapp_url')) {
    /**
     * Tautan chat WhatsApp dari nomor lokal maupun internasional.
     *
     * Nomor Indonesia yang diketik tanpa kode negara (`08…` atau `8…`) diubah
     * menjadi format wa.me (`628…`). Awalan `+62`, `0062`, dan `62` tetap
     * diterima, termasuk bila nomor setelahnya masih diawali `0`. Null
     * dikembalikan bila tidak ada digit sama sekali.
     */
    function whatsapp_url(?string $number): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $number) ?? '';

        if ($digits === '') {
            return null;
        }

        // Awalan akses internasional (0062…) diperlakukan sama dengan 62.
        if (str_starts_with($digits, '0062')) {
            $digits = substr($digits, 2);
        }

        // Nomor yang sudah memakai kode negara tetapi masih menyisakan awalan 0.
        if (str_starts_with($digits, '620')) {
            $digits = '62'.substr($digits, 3);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }
}

if (! function_exists('tanggal_id')) {
    /**
     * Format tanggal berbahasa Indonesia.
     */
    function tanggal_id(mixed $date, string $format = 'd F Y'): string
    {
        if (blank($date)) {
            return '';
        }

        Carbon::setLocale('id');

        return Carbon::parse($date)->translatedFormat($format);
    }
}

if (! function_exists('teks_dari_html')) {
    /**
     * Ubah HTML dari editor kaya menjadi teks biasa yang tetap terbaca.
     *
     * `strip_tags()` saja menghilangkan batas paragraf sehingga kalimat akhir
     * satu paragraf menempel dengan awal paragraf berikutnya. Di sini tag blok
     * dan <br> diterjemahkan menjadi baris baru lebih dahulu. Entity HTML juga
     * didekode agar teks lama yang menyimpan `&lt;br&gt;` tidak tampil mentah.
     */
    function teks_dari_html(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $text = (string) $html;

        // Data lama kadang menyimpan tag sebagai entity, bahkan dua kali.
        for ($i = 0; $i < 2; $i++) {
            $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if ($decoded === $text) {
                break;
            }

            $text = $decoded;
        }

        $text = preg_replace('~<\s*br\s*/?\s*>~i', "\n", $text) ?? $text;
        $text = preg_replace('~<\s*/\s*(?:p|div|li|h[1-6]|blockquote|tr)\s*>~i', "\n", $text) ?? $text;
        $text = strip_tags($text);

        $text = str_replace(["\r\n", "\r", "\u{00A0}"], ["\n", "\n", ' '], $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n */', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}

if (! function_exists('ringkas')) {
    /**
     * Potong teks menjadi ringkasan pendek tanpa tag HTML.
     */
    function ringkas(?string $text, int $limit = 150): string
    {
        return Str::limit(trim(teks_dari_html((string) $text)), $limit);
    }
}

if (! function_exists('ringkas_isi')) {
    /**
     * Ringkasan untuk kartu konten.
     *
     * Ringkasan pendek (excerpt) hanya dipakai bila sudah cukup panjang;
     * jika terlalu pendek, isi yang lebih lengkap dipakai agar deskripsi
     * pada kartu terasa utuh dan tidak seperti teks mikro. Baris baru dari
     * RichEditor tetap dipertahankan agar paragraf tidak saling menempel.
     */
    function ringkas_isi(?string $ringkas, ?string $lengkap = null, int $limit = 200): string
    {
        $ringkas = trim(teks_dari_html($ringkas));

        if (mb_strlen($ringkas) >= (int) round($limit * 0.7)) {
            return Str::limit($ringkas, $limit);
        }

        return Str::limit(trim(teks_dari_html($lengkap)) ?: $ringkas, $limit);
    }
}

if (! function_exists('menu_aktif')) {
    /**
     * Tandai menu yang sedang aktif pada navigasi utama.
     *
     * Sebuah kelompok menu (mis. Publikasi) dinyatakan aktif bila salah satu
     * pola anggotanya cocok, sehingga fungsi ini menerima satu pola atau daftar
     * pola sekaligus.
     *
     * @param  string|list<string>  $pattern
     */
    function menu_aktif(string|array $pattern): string
    {
        foreach ((array) $pattern as $pola) {
            if (request()->is($pola)) {
                return 'is-active';
            }
        }

        return '';
    }
}

if (! function_exists('angka_id')) {
    /**
     * Angka dengan pemisah ribuan gaya Indonesia, mis. 12450 -> "12.450".
     */
    function angka_id(int|float $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }
}

if (! function_exists('persen_id')) {
    /**
     * Persentase dengan pemisah desimal gaya Indonesia, mis. 8.5 -> "8,5%".
     */
    function persen_id(float $value, int $decimals = 1): string
    {
        return number_format($value, $decimals, ',', '.').'%';
    }
}

if (! function_exists('durasi_id')) {
    /**
     * Lama kunjungan (detik) menjadi teks pendek berbahasa Indonesia,
     * mis. 165 -> "2 mnt 45 dtk" dan 45 -> "45 dtk".
     */
    function durasi_id(?int $seconds): string
    {
        if ($seconds === null || $seconds <= 0) {
            return '—';
        }

        $menit = intdiv($seconds, 60);
        $detik = $seconds % 60;

        return match (true) {
            $menit === 0 => $detik.' dtk',
            $detik === 0 => $menit.' mnt',
            default => $menit.' mnt '.$detik.' dtk',
        };
    }
}

if (! function_exists('rentang_bulan')) {
    /**
     * Awal dan akhir sebuah bulan (bawaan: bulan berjalan).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    function rentang_bulan(mixed $bulan = null): array
    {
        $mulai = ($bulan ? Carbon::parse($bulan) : Carbon::now())->startOfMonth();

        return [$mulai, $mulai->copy()->endOfMonth()];
    }
}

if (! function_exists('rentang_tanggal_id')) {
    /**
     * Rentang tanggal berbahasa Indonesia, mis. "1–15 Oktober 2026".
     * Bila kedua tanggal sama, hanya satu tanggal yang ditampilkan.
     */
    function rentang_tanggal_id(mixed $start, mixed $end): string
    {
        if (blank($start) || blank($end)) {
            return '';
        }

        if (Carbon::parse($start)->isSameDay(Carbon::parse($end))) {
            return tanggal_id($start, 'j F Y');
        }

        if (Carbon::parse($start)->isSameMonth(Carbon::parse($end))) {
            return tanggal_id($start, 'j').'–'.tanggal_id($end, 'j F Y');
        }

        return tanggal_id($start, 'j F').' – '.tanggal_id($end, 'j F Y');
    }
}
