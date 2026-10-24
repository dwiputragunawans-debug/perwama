<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Story extends Model
{
    use HasMedia, HasSeoMetadata, HasSlug;

    public const CATEGORIES = [
        'kisah_inspiratif' => 'Kisah Inspiratif',
        'tokoh_perempuan' => 'Tokoh Perempuan',
        'perempuan_maluku' => 'Perempuan Maluku',
        'cerita_komunitas' => 'Cerita Komunitas',
    ];

    protected $fillable = [
        'region_id', 'organization_id', 'program_id', 'title', 'slug',
        'category', 'excerpt', 'content', 'person_name', 'person_photo',
        'person_age', 'cover_image', 'quote', 'journey', 'activities', 'impact',
        'is_featured', 'views', 'meta_title', 'meta_description', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Kisah Inspiratif';
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
