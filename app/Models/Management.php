<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Management extends Model
{
    protected $table = 'managements';

    protected $fillable = [
        'region_id', 'organization_id', 'name', 'position', 'level', 'period',
        'photo', 'email', 'phone', 'bio', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        // Urutan di panel admin (kolom Urutan) menentukan urutan tampil di
        // situs. Id dipakai sebagai penentu terakhir agar susunan tetap stabil
        // bila dua pengurus diberi Urutan yang sama.
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
