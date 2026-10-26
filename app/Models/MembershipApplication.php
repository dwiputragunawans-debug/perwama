<?php

namespace App\Models;

use App\Casts\EncryptedString;
use App\Models\Concerns\MasksNik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipApplication extends Model
{
    use MasksNik, SoftDeletes;

    protected $fillable = [
        'full_name', 'nik', 'birth_place', 'birth_date', 'gender', 'address',
        'city', 'province', 'whatsapp', 'email', 'education', 'occupation',
        'interest_field', 'maluku_origin', 'domicile_region', 'photo',
        'membership_type', 'motivation', 'status', 'admin_notes', 'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'nik' => EncryptedString::class,
            'birth_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function membershipTypeLabel(): string
    {
        return match ($this->membership_type) {
            'relawan' => 'Relawan',
            'mitra' => 'Mitra',
            'kolaborasi' => 'Kolaborasi',
            default => 'Anggota',
        };
    }
}
