<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class News extends Model
{
    use HasMedia, HasSeoMetadata, HasSlug;

    protected $table = 'news';

    public const TYPES = [
        'berita' => 'Berita',
        'artikel' => 'Artikel',
        'opini' => 'Opini',
        'pengumuman' => 'Pengumuman',
    ];

    public const CATEGORIES = [
        'organisasi' => 'Organisasi',
        'kegiatan' => 'Kegiatan',
        'sosial' => 'Sosial',
        'pendidikan' => 'Pendidikan',
        'perempuan' => 'Perempuan',
        'ekonomi' => 'Ekonomi',
        'hukum_ham' => 'Hukum & HAM',
        'budaya' => 'Budaya',
        'pengumuman' => 'Pengumuman',
    ];

    protected $fillable = [
        'program_id', 'region_id', 'title', 'slug', 'type', 'category',
        'excerpt', 'content', 'cover_image', 'author', 'source', 'is_featured',
        'views', 'meta_title', 'meta_description', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Berita';
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Umum';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('published_at');
    }
}
