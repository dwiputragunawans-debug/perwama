<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasMedia, HasSeoMetadata, HasSlug, SoftDeletes;

    public const CATEGORIES = [
        'kegiatan_sosial' => 'Kegiatan Sosial',
        'pelatihan' => 'Pelatihan',
        'seminar' => 'Seminar',
        'organisasi' => 'Organisasi',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'region_id', 'organization_id', 'program_id', 'event_id', 'gallery_id',
        'title', 'slug', 'category', 'excerpt', 'description', 'activity_date',
        'location', 'speaker', 'participant_count', 'cover_image', 'is_featured',
        'views', 'meta_title', 'meta_description', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
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

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Lainnya';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('activity_date')->orderByDesc('published_at');
    }
}
