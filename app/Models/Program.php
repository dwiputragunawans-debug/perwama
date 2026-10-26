<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasMedia, HasSeoMetadata, HasSlug, SoftDeletes;

    protected $fillable = [
        'program_category_id', 'region_id', 'title', 'slug', 'type', 'excerpt',
        'description', 'goal', 'objectives', 'target_participants', 'achievements',
        'cover_image', 'document', 'budget', 'fiscal_year', 'status',
        'is_featured', 'views', 'sort_order', 'meta_title', 'meta_description',
        'published_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'budget' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProgramCategory::class, 'program_category_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'ongoing' => 'Berjalan',
            'completed' => 'Selesai',
            default => 'Direncanakan',
        };
    }
}
