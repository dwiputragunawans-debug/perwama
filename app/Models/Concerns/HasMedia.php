<?php

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Lampiran berkas (dokumen/gambar) milik sebuah konten.
 *
 * Berkas diunggah lewat panel admin (Pengaturan → Media) lalu ditampilkan pada
 * detail konten sebagai daftar "Lampiran & Berkas" — lihat
 * resources/views/partials/media-attachments.blade.php.
 */
trait HasMedia
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Lampiran yang layak ditampilkan ke publik (memiliki lokasi berkas).
     */
    public function publicMedia(): MorphMany
    {
        return $this->media()->whereNotNull('path')->where('path', '!=', '');
    }
}
