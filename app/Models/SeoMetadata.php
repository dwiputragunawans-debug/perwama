<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMetadata extends Model
{
    protected $table = 'seo_metadata';

    protected $fillable = [
        'model_type', 'model_id', 'meta_title', 'meta_description',
        'meta_keywords', 'og_image', 'canonical_url',
    ];

    /**
     * Konten yang mendukung metadata SEO dari panel.
     *
     * Halaman statis (Beranda, Tentang, Kontak, dan sebagainya) memakai nilai
     * bawaan layout; hanya halaman detail konten yang dapat ditimpa di sini.
     *
     * @var array<class-string<Model>, string>
     */
    public const SUPPORTED_MODELS = [
        News::class => 'Berita',
        Program::class => 'Program',
        Story::class => 'Cerita',
        Activity::class => 'Kegiatan',
        Event::class => 'Agenda',
        Gallery::class => 'Galeri',
        Video::class => 'Video',
        Publication::class => 'Publikasi',
        Region::class => 'Wilayah',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Kolom judul yang dipakai untuk label pilihan konten di panel.
     */
    public static function labelColumnFor(?string $modelType): string
    {
        return $modelType === Region::class ? 'name' : 'title';
    }

    /**
     * Label jenis konten yang mudah dibaca admin.
     */
    public function modelLabel(): string
    {
        return self::SUPPORTED_MODELS[$this->model_type] ?? class_basename((string) $this->model_type);
    }

    /**
     * Judul konten yang dimiliki catatan ini (bila masih ada).
     */
    public function targetTitle(): ?string
    {
        $column = self::labelColumnFor($this->model_type);

        return $this->model?->{$column};
    }

    /**
     * URL gambar OG, menerima path disk `public` maupun URL absolut.
     */
    public function ogImageUrl(): ?string
    {
        return media_url($this->og_image);
    }
}
