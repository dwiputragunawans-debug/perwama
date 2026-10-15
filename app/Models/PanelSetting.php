<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanelSetting extends Model
{
    protected $fillable = [
        'brand_logo',
        'brand_logo_height',
        'brand_name',
        'brand_tagline',
        'favicon',
        'login_background',
        'login_background_color',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'brand_logo_height' => '2.5rem',
        'login_background_color' => '#152038',
    ];

    /**
     * Cache pengaturan selama satu siklus permintaan.
     */
    protected static ?self $cached = null;

    /**
     * Ambil (atau buat) satu-satunya baris pengaturan tampilan panel.
     */
    public static function current(): self
    {
        if (static::$cached instanceof self) {
            return static::$cached;
        }

        return static::$cached = static::query()->firstOrCreate(['id' => 1], [
            'brand_logo_height' => '2.5rem',
            'login_background_color' => '#152038',
        ]);
    }

    /**
     * Bersihkan cache pengaturan setelah perubahan.
     */
    public static function flushCache(): void
    {
        static::$cached = null;
    }

    /**
     * Nama brand yang tampil di samping logo panel. Bila pengaturan tersendiri
     * kosong, nilai diambil dari profil organisasi (sumber yang sama dengan
     * header website) agar konsisten.
     */
    public function displayName(): string
    {
        $profile = OrganizationProfile::current();

        return $this->brand_name
            ?: $profile->abbreviation
            ?: $profile->name
            ?: 'PERWAMA INA';
    }

    /**
     * Slogan yang tampil di bawah nama brand (panel admin & header website).
     * Bila kosong, nama organisasi dari Profil Organisasi dipakai agar sama
     * dengan header website.
     */
    public function displayTagline(): ?string
    {
        $profile = OrganizationProfile::current();

        return $this->brand_tagline ?: $profile->name ?: $profile->tagline;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
