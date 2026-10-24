<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    protected $table = 'media';

    /**
     * Konten yang dapat memiliki lampiran berkas. Daftar ini sengaja sama
     * dengan konten yang mendukung SEO agar admin memilih dari nama yang
     * konsisten, bukan mengetik nama kelas secara manual.
     *
     * @var array<class-string<Model>, string>
     */
    public const SUPPORTED_MODELS = SeoMetadata::SUPPORTED_MODELS;

    protected $fillable = [
        'model_type', 'model_id', 'collection', 'name', 'file_name',
        'mime_type', 'path', 'disk', 'size', 'alt', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Lengkapi metadata berkas dari penyimpanan agar admin cukup mengunggah
     * berkas (nama, tipe MIME, dan ukuran terisi otomatis).
     */
    protected static function booted(): void
    {
        static::saving(function (Media $media): void {
            if (blank($media->path)) {
                return;
            }

            $media->file_name = $media->file_name ?: basename($media->path);
            $media->name = $media->name ?: pathinfo($media->file_name, PATHINFO_FILENAME);
            $media->disk = $media->disk ?: 'public';

            if (Str::startsWith($media->path, ['http://', 'https://', '//'])) {
                return;
            }

            $disk = Storage::disk($media->disk);

            if (! $disk->exists($media->path)) {
                return;
            }

            $media->size = $media->size ?: $disk->size($media->path);
            $media->mime_type = $media->mime_type ?: $disk->mimeType($media->path);
        });
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * URL publik berkas. Path disk `public` memakai helper media_url agar
     * berkas yang disimpan di repositori (public/images/...) tetap terbaca.
     */
    public function url(): ?string
    {
        if (blank($this->path)) {
            return null;
        }

        if (Str::startsWith($this->path, ['http://', 'https://', '//', '/'])) {
            return $this->path;
        }

        if (($this->disk ?: 'public') === 'public') {
            return media_url($this->path);
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return Str::startsWith((string) $this->mime_type, 'image/')
            || in_array(Str::lower(pathinfo((string) $this->path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);
    }

    /**
     * Ukuran berkas dalam satuan yang mudah dibaca (KB/MB).
     */
    public function sizeLabel(): ?string
    {
        if (blank($this->size)) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->size;
        $index = 0;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return round($size, $index > 0 ? 1 : 0).' '.$units[$index];
    }

    /**
     * Nama berkas yang ditampilkan ke pengunjung.
     */
    public function displayName(): string
    {
        return $this->name ?: $this->file_name ?: basename((string) $this->path);
    }

    /**
     * Label jenis konten pemilik berkas.
     */
    public function modelLabel(): string
    {
        return self::SUPPORTED_MODELS[$this->model_type] ?? class_basename((string) $this->model_type);
    }

    /**
     * Judul konten pemilik berkas (null bila konten sudah dihapus).
     */
    public function targetTitle(): ?string
    {
        $column = SeoMetadata::labelColumnFor($this->model_type);

        return $this->model?->{$column};
    }
}
