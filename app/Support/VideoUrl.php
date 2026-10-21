<?php

namespace App\Support;

/**
 * Normalisasi tautan video untuk dipasang pada iframe.
 *
 * YouTube menolak tautan halaman tonton (watch, live, shorts, youtu.be) bila
 * dipakai langsung sebagai `src` iframe. Semua bentuk tautan tersebut harus
 * diubah ke alamat `/embed/{id}` agar video tampil.
 */
final class VideoUrl
{
    private const YOUTUBE_ID_PATTERN = '~(?:youtube(?:-nocookie)?\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([\w-]+)~i';

    /**
     * Ubah tautan YouTube/Vimeo menjadi URL embed.
     */
    public static function embed(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if ($youtubeId = self::youtubeId($url)) {
            return 'https://www.youtube.com/embed/'.$youtubeId;
        }

        if (preg_match('~vimeo\.com/(\d+)~i', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $url;
    }

    /**
     * Ambil ID video dari berbagai bentuk tautan YouTube.
     */
    public static function youtubeId(?string $url): ?string
    {
        if (blank($url) || ! preg_match(self::YOUTUBE_ID_PATTERN, $url, $m)) {
            return null;
        }

        return $m[1];
    }
}
