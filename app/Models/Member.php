<?php

namespace App\Models;

use App\Casts\EncryptedString;
use App\Models\Concerns\MasksNik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use MasksNik;

    /**
     * Label status keanggotaan dalam bahasa Indonesia.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        'pending' => 'Menunggu',
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif',
        'rejected' => 'Ditolak',
    ];

    protected $fillable = [
        'user_id', 'organization_id', 'region_id', 'member_number', 'full_name',
        'nik', 'birth_place', 'birth_date', 'gender', 'address', 'city',
        'province', 'whatsapp', 'email', 'education', 'occupation',
        'interest_field', 'maluku_origin', 'domicile_region', 'photo', 'status',
        'joined_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'nik' => EncryptedString::class,
            'birth_date' => 'date',
            'joined_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Label status anggota, mis. "Aktif" untuk status `active`.
     */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? 'Menunggu';
    }
}
