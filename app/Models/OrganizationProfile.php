<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'abbreviation', 'tagline', 'slogan', 'vision', 'mission',
        'about_card_image', 'vision_card_image', 'mission_card_image',
        'short_description', 'description', 'address', 'city', 'province',
        'postal_code', 'email', 'phone', 'whatsapp', 'founded_year', 'logo',
        'logo_white', 'facebook', 'instagram', 'youtube', 'twitter', 'tiktok',
        'map_embed',
    ];

    /**
     * Misi dibaca sebagai larik poin. Formulir admin memakai textarea, sehingga
     * teks dipisah per baris saat disimpan; data lama yang sudah berupa larik
     * JSON tetap terbaca dengan benar.
     */
    protected function mission(): Attribute
    {
        return Attribute::make(
            get: fn ($value): array => static::normalizeMission($value),
            set: function ($value): ?string {
                $items = static::normalizeMission($value);

                return $items === [] ? null : json_encode($items, JSON_UNESCAPED_UNICODE);
            },
        );
    }

    /**
     * Ubah nilai misi apa pun (larik, JSON, atau teks multi-baris) menjadi larik poin.
     *
     * @return array<int, string>
     */
    protected static function normalizeMission(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            // Terima nilai JSON (larik maupun string) atau teks biasa.
            if (is_array($decoded) || is_string($decoded)) {
                $value = $decoded;
            }
        }

        if (is_string($value)) {
            $value = preg_split('/\r\n|\r|\n/', $value);
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn ($item): string => trim((string) $item), $value),
            static fn (string $item): bool => $item !== '',
        ));
    }

    /**
     * Cache profil organisasi selama satu siklus permintaan.
     */
    protected static ?self $cached = null;

    /**
     * Bersihkan cache profil organisasi setelah perubahan.
     */
    public static function flushCache(): void
    {
        static::$cached = null;
    }

    /**
     * Ambil (atau buat) satu-satunya baris profil organisasi.
     */
    public static function current(): self
    {
        if (static::$cached instanceof self) {
            return static::$cached;
        }

        /*
         * Profil adalah data tunggal. Baris yang terlanjur di-soft-delete
         * tetap dibaca agar halaman publik tidak kehilangan identitas dan
         * `firstOrCreate(['id' => 1])` tidak menabrak primary key. Pemulihan
         * tetap dapat dilakukan dari filter data terhapus di panel admin.
         */
        return static::$cached = static::withTrashed()->firstOrCreate(['id' => 1], [
            'name' => 'Persatuan Wanita Maluku Indonesia',
            'abbreviation' => 'PERWAMA INA',
        ]);
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
        static::restored(fn () => static::flushCache());
    }
}
