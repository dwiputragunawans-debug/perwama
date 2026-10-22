<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasSlug;

    protected $fillable = [
        'region_id', 'parent_id', 'name', 'slug', 'type', 'leader_name',
        'secretary_name', 'address', 'phone', 'email', 'whatsapp', 'facebook',
        'instagram', 'youtube', 'logo', 'cover_image', 'description',
        'founded_at', 'member_count', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'founded_at' => 'date',
            'member_count' => 'integer',
        ];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function managements(): HasMany
    {
        return $this->hasMany(Management::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Deskripsi siap tampil di halaman publik.
     *
     * Deskripsi baru diisi lewat RichEditor sehingga sudah berupa HTML.
     * Deskripsi lama masih berupa teks biasa; baris baru diubah menjadi <br>
     * agar tidak menyatu dalam satu paragraf. Sebagian data lama juga masih
     * menyimpan tag sebagai entity, sehingga perlu didekode lebih dahulu.
     */
    public function descriptionHtml(): ?string
    {
        if (blank($this->description)) {
            return null;
        }

        $description = (string) $this->description;

        if (strip_tags($description) === $description) {
            $decoded = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Data lama seperti `&lt;br&gt;` tetap harus tampil sebagai baris baru.
            if (strip_tags($decoded) !== $decoded) {
                return $decoded;
            }

            return nl2br(e($description));
        }

        return $description;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'dpp' => 'Dewan Pimpinan Pusat (DPP)',
            'dpw' => 'Dewan Pimpinan Daerah (DPD)',
            'pc' => 'Pimpinan Cabang (PC)',
            default => 'Organisasi',
        };
    }
}
