<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tokoh organisasi yang tampil pada bagian "Tokoh Organisasi" di halaman
 * Tentang Kami. Umumnya berisi para pendiri PERWAMA INA beserta fotonya.
 */
class OrganizationFigure extends Model
{
    protected $fillable = [
        'name', 'position', 'photo', 'sort_order', 'is_active',
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

    public function scopeOrdered($query)
    {
        // Urutan di panel admin (kolom Urutan) menentukan urutan tampil di
        // situs. Id dipakai sebagai penentu terakhir agar susunan tetap stabil
        // bila dua tokoh diberi Urutan yang sama.
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
