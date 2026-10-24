<?php

namespace App\Support;

use DOMAttr;
use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMProcessingInstruction;

/**
 * Pembersih HTML sebelum ditampilkan ke pengunjung.
 *
 * Isi berita, program, kegiatan, cerita, video, dan deskripsi wilayah diisi
 * lewat RichEditor Filament lalu dirender dengan `{!! !!}`. Filament sendiri
 * mengingatkan agar keluaran editor disanitasi lebih dulu: HTML dapat berasal
 * dari data lama, hasil impor, atau permintaan yang dimanipulasi sebelum
 * dikirim. Tanpa pembersihan, `<script>` atau atribut `on*` pada isi tersebut
 * berjalan di peramban pengunjung.
 *
 * Pendekatan: allowlist. Semua tag/atribut di luar daftar dibuang — tag yang
 * tidak dikenal dilepas tetapi teksnya dipertahankan, sedangkan tag berbahaya
 * (`<script>`, `<style>`, `<object>`, `<iframe>` pada konten biasa) dibuang
 * beserta isinya. Pemetaan dilakukan dengan DOMDocument sehingga trik seperti
 * `java&#115;cript:` atau atribut bersarang tetap ternormalisasi lebih dulu.
 */
class HtmlSanitizer
{
    /**
     * Tag yang boleh tampil pada konten artikel.
     */
    private const TAGS = [
        'p', 'br', 'span', 'div', 'hr',
        'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins', 'sub', 'sup', 'code', 'pre',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'blockquote', 'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
    ];

    /**
     * Tag yang dibuang bersama seluruh isinya (bukan sekadar dilepas tag-nya).
     */
    private const TAGS_DIBUANG = [
        'script', 'style', 'noscript', 'template', 'object', 'embed', 'applet',
        'form', 'input', 'button', 'select', 'option', 'textarea', 'link', 'meta',
        'base', 'svg', 'math', 'frame', 'frameset', 'audio', 'video', 'source',
    ];

    /**
     * Atribut per tag. `*` berlaku untuk semua tag.
     *
     * @var array<string, list<string>>
     */
    private const ATRIBUT = [
        '*' => ['class', 'title', 'style'],
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'ol' => ['start'],
        'li' => ['value'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan', 'scope'],
        'iframe' => ['src', 'width', 'height', 'loading', 'allowfullscreen', 'referrerpolicy', 'frameborder'],
    ];

    /**
     * Properti CSS yang dipertahankan. Filament memakai `text-align` untuk
     * perataan paragraf; sisanya hanya format teks. Properti lain dibuang
     * agar tidak ada `position: fixed` atau `background: url(...)`.
     */
    private const PROPERTI_GAYA = [
        'text-align', 'font-weight', 'font-style', 'text-decoration',
    ];

    /**
     * Host yang boleh dipakai sebagai peta tersemat.
     */
    private const HOST_SEMATAN = [
        'google.com', 'google.co.id', 'openstreetmap.org', 'openstreetmap.fr',
        'maps.app.goo.gl', 'arcgis.com',
    ];

    /**
     * HTML aman untuk ditampilkan (konten artikel, deskripsi, dsb.).
     */
    public static function clean(?string $html): string
    {
        return self::sanitize($html, false);
    }

    /**
     * HTML aman yang masih memperbolehkan iframe peta (kolom `map_embed`).
     */
    public static function embed(?string $html): ?string
    {
        $bersih = trim(self::sanitize($html, true));

        return $bersih === '' ? null : $bersih;
    }

    private static function sanitize(?string $html, bool $izinkanSematan): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $dokumen = new DOMDocument('1.0', 'UTF-8');

        $sebelumnya = libxml_use_internal_errors(true);

        $dokumen->loadHTML(
            '<?xml encoding="utf-8" ?><div data-akar-pembersih>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($sebelumnya);

        $akar = $dokumen->documentElement;

        /*
         * HTML yang sama sekali tidak dapat diparse: tampilkan sebagai teks
         * biasa supaya tidak ada yang lolos tanpa pemeriksaan.
         */
        if (! $akar instanceof DOMElement) {
            return nl2br(htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        foreach (iterator_to_array($akar->childNodes) as $anak) {
            self::bersihkanNode($anak, $izinkanSematan);
        }

        $hasil = '';

        foreach ($akar->childNodes as $anak) {
            $hasil .= $dokumen->saveHTML($anak);
        }

        return trim($hasil);
    }

    private static function bersihkanNode(DOMNode $node, bool $izinkanSematan): void
    {
        if ($node instanceof DOMComment || $node instanceof DOMProcessingInstruction) {
            $node->parentNode?->removeChild($node);

            return;
        }

        if (! $node instanceof DOMElement) {
            return;
        }

        $tag = strtolower($node->nodeName);

        if (in_array($tag, self::TAGS_DIBUANG, true)) {
            $node->parentNode?->removeChild($node);

            return;
        }

        if ($tag === 'iframe') {
            if (! $izinkanSematan || ! self::sematanDiizinkan($node)) {
                $node->parentNode?->removeChild($node);

                return;
            }

            self::bersihkanAtribut($node, $tag);

            // Iframe tidak memiliki isi yang berarti bagi pengunjung.
            while ($node->firstChild !== null) {
                $node->removeChild($node->firstChild);
            }

            return;
        }

        if (! in_array($tag, self::TAGS, true)) {
            /*
             * Tag tidak dikenal (mis. <section> atau <font>): isi tetap
             * dipertahankan, hanya pembungkusnya yang dilepas. Anak dibersihkan
             * lebih dulu karena setelah dilepas ia menjadi milik induk.
             */
            foreach (iterator_to_array($node->childNodes) as $anak) {
                self::bersihkanNode($anak, $izinkanSematan);
            }

            self::lepasPembungkus($node);

            return;
        }

        self::bersihkanAtribut($node, $tag);

        foreach (iterator_to_array($node->childNodes) as $anak) {
            self::bersihkanNode($anak, $izinkanSematan);
        }
    }

    private static function bersihkanAtribut(DOMElement $element, string $tag): void
    {
        $diizinkan = array_merge(self::ATRIBUT['*'], self::ATRIBUT[$tag] ?? []);

        foreach (iterator_to_array($element->attributes) as $atribut) {
            if (! $atribut instanceof DOMAttr) {
                continue;
            }

            $nama = strtolower($atribut->nodeName);
            $nilai = (string) $atribut->nodeValue;

            // Semua penangan kejadian (onclick, onerror, …) selalu dibuang.
            if (str_starts_with($nama, 'on') || ! in_array($nama, $diizinkan, true)) {
                $element->removeAttribute($atribut->nodeName);

                continue;
            }

            switch ($nama) {
                case 'href':
                case 'src':
                    $url = self::urlAman($nilai, $nama === 'src' && $tag === 'img');

                    if ($url === null) {
                        $element->removeAttribute($atribut->nodeName);
                    } else {
                        $element->setAttribute($nama, $url);
                    }

                    break;

                case 'style':
                    $gaya = self::gayaAman($nilai);

                    if ($gaya === '') {
                        $element->removeAttribute($atribut->nodeName);
                    } else {
                        $element->setAttribute($nama, $gaya);
                    }

                    break;

                case 'target':
                    if (! in_array($nilai, ['_blank', '_self'], true)) {
                        $element->removeAttribute($atribut->nodeName);
                    }

                    break;

                case 'loading':
                    if (! in_array($nilai, ['lazy', 'eager'], true)) {
                        $element->removeAttribute($atribut->nodeName);
                    }

                    break;

                case 'scope':
                    if (! in_array($nilai, ['row', 'col', 'rowgroup', 'colgroup'], true)) {
                        $element->removeAttribute($atribut->nodeName);
                    }

                    break;

                case 'allowfullscreen':
                    // Atribut boolean: nilai apa pun diperlakukan sebagai aktif.
                    $element->setAttribute($nama, 'allowfullscreen');

                    break;

                case 'referrerpolicy':
                    if (! in_array($nilai, ['no-referrer', 'no-referrer-when-downgrade', 'origin', 'strict-origin-when-cross-origin'], true)) {
                        $element->removeAttribute($atribut->nodeName);
                    }

                    break;

                case 'frameborder':
                    if (! in_array($nilai, ['0', '1'], true)) {
                        $element->removeAttribute($atribut->nodeName);
                    }

                    break;

                case 'width':
                case 'height':
                case 'colspan':
                case 'rowspan':
                case 'start':
                case 'value':
                    if (! preg_match('/^\d{1,4}$/', trim($nilai))) {
                        $element->removeAttribute($atribut->nodeName);
                    }

                    break;
            }
        }

        /*
         * Tautan ke tab baru tanpa `noopener` dapat mengendalikan halaman
         * pembuka lewat `window.opener` (tabnabbing).
         */
        if ($tag === 'a' && strtolower($element->getAttribute('target')) === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }

        if ($tag === 'iframe') {
            if (! $element->hasAttribute('loading')) {
                $element->setAttribute('loading', 'lazy');
            }

            if (! $element->hasAttribute('title')) {
                $element->setAttribute('title', 'Peta lokasi');
            }
        }
    }

    /**
     * Skema URL yang aman. `data:` hanya untuk gambar dan hanya format raster.
     */
    private static function urlAman(string $nilai, bool $dataGambar = false): ?string
    {
        // Karakter kendali (termasuk baris baru) sering dipakai memecah
        // "javascript:" agar tidak terdeteksi pemeriksaan sederhana.
        $url = trim((string) preg_replace('/[\x00-\x1F\x7F]/', '', $nilai));

        if ($url === '') {
            return null;
        }

        if ($dataGambar && preg_match('#^data:image/(png|jpe?g|gif|webp|avif);base64,[A-Za-z0-9+/=\s]+$#i', $url)) {
            return $url;
        }

        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $url, $cocok)) {
            $skema = strtolower($cocok[1]);

            return in_array($skema, ['http', 'https', 'mailto', 'tel'], true) ? $url : null;
        }

        // URL relatif (`/berita`, `#bagian`) dan protokol-relatif (`//cdn…`).
        return $url;
    }

    private static function gayaAman(string $nilai): string
    {
        $aturan = [];

        foreach (explode(';', $nilai) as $deklarasi) {
            [$properti, $isi] = array_pad(explode(':', $deklarasi, 2), 2, '');

            $properti = strtolower(trim($properti));
            $isi = trim($isi);

            if ($isi === '' || ! in_array($properti, self::PROPERTI_GAYA, true)) {
                continue;
            }

            /*
             * Deklarasi berbahaya dibuang satu per satu, bukan seluruh atribut,
             * agar perataan teks yang sah tidak ikut hilang.
             */
            if (preg_match('/expression|javascript:|vbscript:|url\s*\(|@import|behavior\s*:|binding\s*:/i', $isi)) {
                continue;
            }

            if (! preg_match('/^[A-Za-z0-9\s#%,.\-]+$/', $isi)) {
                continue;
            }

            $aturan[] = $properti.': '.$isi;
        }

        return implode('; ', $aturan);
    }

    /**
     * Hanya peta resmi Google/OpenStreetMap yang boleh disematkan; host di luar
     * daftar (termasuk iframe iklan atau halaman pihak ketiga) dibuang.
     */
    private static function sematanDiizinkan(DOMElement $iframe): bool
    {
        $src = trim($iframe->getAttribute('src'));

        $skema = parse_url($src, PHP_URL_SCHEME);
        $host = parse_url($src, PHP_URL_HOST);

        if (! is_string($host) || $host === '' || ! is_string($skema)) {
            return false;
        }

        if (! in_array(strtolower($skema), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($host);

        foreach (self::HOST_SEMATAN as $diizinkan) {
            if ($host === $diizinkan || str_ends_with($host, '.'.$diizinkan)) {
                return true;
            }
        }

        return false;
    }

    private static function lepasPembungkus(DOMElement $element): void
    {
        $induk = $element->parentNode;

        if ($induk === null) {
            return;
        }

        while ($element->firstChild !== null) {
            $induk->insertBefore($element->firstChild, $element);
        }

        $induk->removeChild($element);
    }
}
