<?php

namespace App\Models\Concerns;

use App\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Konten yang dapat memiliki satu catatan SEO tambahan.
 *
 * Data disimpan di tabel `seo_metadata` (polimorfik) dan dibaca oleh
 * resources/views/partials/seo.blade.php. Halaman tanpa catatan SEO tetap
 * memakai nilai bawaan dari layout (judul halaman, ringkasan profil, dsb.).
 */
trait HasSeoMetadata
{
    public function seoMetadata(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'model');
    }
}
