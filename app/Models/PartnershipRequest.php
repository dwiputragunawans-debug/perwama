<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnershipRequest extends Model
{
    protected $fillable = [
        'name', 'organization_name', 'email', 'phone', 'type', 'message',
        'status', 'admin_notes',
    ];

    public function typeLabel(): string
    {
        return match ($this->type) {
            'anggota' => 'Menjadi Anggota',
            'relawan' => 'Menjadi Relawan',
            'kolaborasi' => 'Berkolaborasi',
            default => 'Menjadi Mitra',
        };
    }
}
