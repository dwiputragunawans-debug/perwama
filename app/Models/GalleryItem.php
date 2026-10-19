<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryItem extends Model
{
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
     * Data lama menyimpan tautan tonton (mis. youtube.com/watch?v=...), yang
     * ditolak YouTube bila dipakai langsung sebagai `src` iframe. URL tersebut
     * dinormalkan ke bentuk embed agar video tampil di halaman galeri.
     */
    public function embedUrl(): ?string
    {
        $url = $this->video_url;

        if (blank($url)) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]+)~i', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(\d+)~i', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $url;
    }
}
