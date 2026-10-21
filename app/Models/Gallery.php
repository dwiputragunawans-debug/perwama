<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    use HasSlug;

    protected $fillable = [
        'region_id', 'program_id', 'title', 'slug', 'description',
        'album_category_id', 'cover_image', 'event_date', 'is_featured',
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

    public function albumCategory(): BelongsTo
    {
        return $this->belongsTo(AlbumCategory::class, 'album_category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }

    public function albumLabel(): string
    {
        return $this->albumCategory?->name ?? 'Galeri';
    }

    /**
     * Deskripsi siap tampil di halaman publik.
     *
     * Deskripsi baru diisi lewat RichEditor sehingga sudah berupa HTML.
     * Deskripsi lama (mis. dari seeder) masih berupa teks biasa; untuk itu
     * baris baru diubah menjadi <br> agar tidak menyatu dalam satu paragraf.
     */
    public function descriptionHtml(): ?string
    {
        if (blank($this->description)) {
            return null;
        }

        return strip_tags($this->description) === $this->description
            ? nl2br(e($this->description))
            : $this->description;
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
