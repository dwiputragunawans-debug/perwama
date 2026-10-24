<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasMedia, HasSeoMetadata, HasSlug;

    protected $fillable = [
        'region_id', 'organization_id', 'program_id', 'title', 'slug',
        'description', 'start_date', 'start_time', 'end_date', 'end_time',
        'location', 'poster', 'speaker', 'status', 'is_featured', 'views',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
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

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Agenda yang sudah dijadwalkan terbit.
     *
     * Sama seperti News/Program/Activity/Story/Video: agenda tanpa
     * `published_at` atau yang tanggal terbitnya masih di masa depan tidak
     * boleh muncul di situs publik, termasuk bila slug-nya diketahui.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', ['upcoming', 'ongoing'])
            ->orderBy('start_date');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('start_date');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'ongoing' => 'Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Akan Datang',
        };
    }
}
