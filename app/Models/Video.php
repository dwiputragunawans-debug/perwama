<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasSlug;

    public const CATEGORIES = [
        'profil' => 'Profil Organisasi',
        'kegiatan' => 'Kegiatan',
        'program' => 'Program',
        'pelatihan' => 'Pelatihan',
        'sosialisasi' => 'Sosialisasi',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'title', 'slug', 'description', 'url', 'thumbnail', 'category',
        'is_featured', 'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Video';
    }

    /**
     * Ubah URL YouTube/Vimeo menjadi URL embed.
     */
    public function embedUrl(): ?string
    {
        $url = $this->url;

        if (blank($url)) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([\w-]+)~i', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(\d+)~i', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $url;
    }

    public function thumbnailUrl(): ?string
    {
        if (filled($this->thumbnail)) {
            return str_starts_with($this->thumbnail, 'http')
                ? $this->thumbnail
                : asset('storage/'.$this->thumbnail);
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([\w-]+)~i', (string) $this->url, $m)) {
            return 'https://img.youtube.com/vi/'.$m[1].'/hqdefault.jpg';
        }

        return null;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at');
    }
}
