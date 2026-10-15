<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'hero_images',
        'region_section_title',
        'region_section_description',
    ];

    protected function casts(): array
    {
        return [
            'hero_images' => 'array',
        ];
    }

    /**
     * Cache pengaturan selama satu siklus permintaan.
     */
    protected static ?self $cached = null;

    /**
     * Ambil (atau buat) satu-satunya baris pengaturan tampilan website.
     */
    public static function current(): self
    {
        if (static::$cached instanceof self) {
            return static::$cached;
        }

        return static::$cached = static::query()->firstOrCreate(['id' => 1], [
            'hero_images' => [],
        ]);
    }

    /**
     * Gambar hero beranda (hasil unggahan) sebagai URL siap pakai.
     *
     * @return Collection<int, string>
     */
    public function heroImageUrls(): Collection
    {
        return collect($this->hero_images ?? [])
            ->map(fn (?string $path): ?string => media_url($path))
            ->filter()
            ->values();
    }

    /**
     * Bersihkan cache pengaturan setelah perubahan.
     */
    public static function flushCache(): void
    {
        static::$cached = null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
