<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    use HasSlug;

    public const ALBUMS = [
        'hut_perwama_ina' => 'HUT PERWAMA INA',
        'pelatihan' => 'Pelatihan',
        'bakti_sosial' => 'Bakti Sosial',
        'seminar' => 'Seminar',
        'rapat_organisasi' => 'Rapat Organisasi',
        'kunjungan' => 'Kunjungan',
        'kegiatan_daerah' => 'Kegiatan Daerah',
        'budaya' => 'Budaya',
    ];

    protected $fillable = [
        'region_id', 'program_id', 'title', 'slug', 'description',
        'album_category', 'cover_image', 'event_date', 'is_featured',
        'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'event_date' => 'date',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }

    public function albumLabel(): string
    {
        return self::ALBUMS[$this->album_category] ?? 'Galeri';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('event_date');
    }
}
