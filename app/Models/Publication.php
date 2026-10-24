<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Publication extends Model
{
    use HasMedia, HasSeoMetadata, HasSlug;

    public const CATEGORIES = [
        'laporan' => 'Laporan',
        'buku' => 'Buku',
        'modul' => 'Modul',
        'materi_pendidikan' => 'Materi Pendidikan',
        'kajian' => 'Kajian',
        'dokumentasi' => 'Dokumentasi',
        'panduan' => 'Panduan',
    ];

    protected $fillable = [
        'program_id', 'title', 'slug', 'category', 'description', 'file',
        'cover_image', 'year', 'page_count', 'file_size', 'download_count',
        'is_featured', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Dokumen';
    }

    /**
     * Ukuran berkas dalam satuan yang mudah dibaca (KB/MB).
     */
    public function fileSizeLabel(): ?string
    {
        if (blank($this->file_size)) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->file_size;
        $index = 0;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return round($size, $index > 0 ? 1 : 0).' '.$units[$index];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('published_at');
    }
}
