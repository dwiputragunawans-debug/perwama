<?php

namespace App\Models;

use App\Support\VideoUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalleryItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'gallery_id', 'type', 'file', 'video_url', 'thumbnail', 'caption', 'sort_order',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * URL embed untuk iframe video.
     *
     * Data lama menyimpan tautan tonton (mis. youtube.com/watch?v=... atau
     * youtube.com/live/...), yang ditolak YouTube bila dipakai langsung sebagai
     * `src` iframe. URL tersebut dinormalkan ke bentuk embed agar video tampil
     * di halaman galeri.
     */
    public function embedUrl(): ?string
    {
        return VideoUrl::embed($this->video_url);
    }
}
