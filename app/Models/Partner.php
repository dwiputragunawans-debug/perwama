<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    /**
     * Jenis mitra yang dipakai panel dan ditampilkan apa adanya di situs.
     *
     * @var array<string, string>
     */
    public const TYPES = [
        'lembaga_pemerintah' => 'Lembaga Pemerintah',
        'lembaga' => 'Lembaga',
        'kampus' => 'Kampus',
        'korporasi' => 'Korporasi',
        'yayasan' => 'Yayasan',
        'komunitas' => 'Komunitas',
        'media' => 'Media',
        'internasional' => 'Lembaga Internasional',
    ];

    protected $fillable = [
        'name', 'logo', 'website', 'type', 'description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Mitra yang boleh tampil di situs publik (aktif di panel).
     */
    public function scopePublic($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Label jenis mitra; nilai lama yang tidak dikenal tetap ditampilkan.
     */
    public function typeLabel(): ?string
    {
        if (blank($this->type)) {
            return null;
        }

        return self::TYPES[$this->type] ?? $this->type;
    }
}
