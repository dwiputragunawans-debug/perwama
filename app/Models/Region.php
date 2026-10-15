<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasSlug;

    /**
     * Jenis wilayah yang dipakai situs, dipetakan ke label bahasa Indonesia.
     *
     * @var array<string, string>
     */
    public const TYPES = [
        'province' => 'Provinsi',
        'regency' => 'Kabupaten',
        'city' => 'Kota',
    ];

    protected $fillable = [
        'parent_id', 'name', 'slug', 'type', 'province', 'island', 'description',
        'latitude', 'longitude', 'cover_image', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeProvinces($query)
    {
        return $query->where('type', 'province');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Label jenis wilayah dalam bahasa Indonesia (mis. "Provinsi").
     */
    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Wilayah';
    }
}
