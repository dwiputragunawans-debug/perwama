<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    use HasSlug;

    protected $fillable = [
        'organization_id', 'region_id', 'name', 'slug', 'type', 'leader_name',
        'address', 'phone', 'email', 'description', 'member_count', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'member_count' => 'integer',
        ];
    }

    protected function slugSource(): string
    {
        return 'name';
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
        return $query->where('is_active', true);
    }
}
