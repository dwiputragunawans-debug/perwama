<?php

namespace Tests\Feature;

use App\Models\GalleryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tautan tonton YouTube/Vimeo tidak dapat dipakai langsung sebagai `src`
 * iframe. Halaman galeri harus memakai URL embed agar video benar-benar
 * tampil, bukan kotak abu-abu.
 */
class GalleryItemVideoEmbedTest extends TestCase
{
    use RefreshDatabase;

    public function test_tautan_video_dinormalkan_ke_url_embed(): void
    {
        $kasus = [
            'https://www.youtube.com/watch?v=ScMzIvxBSi4' => 'https://www.youtube.com/embed/ScMzIvxBSi4',
            'https://youtu.be/ScMzIvxBSi4?t=10' => 'https://www.youtube.com/embed/ScMzIvxBSi4',
            'https://www.youtube.com/embed/ScMzIvxBSi4' => 'https://www.youtube.com/embed/ScMzIvxBSi4',
            'https://www.youtube.com/shorts/ScMzIvxBSi4' => 'https://www.youtube.com/embed/ScMzIvxBSi4',
            'https://www.youtube.com/live/ScMzIvxBSi4?si=contoh' => 'https://www.youtube.com/embed/ScMzIvxBSi4',
            'https://vimeo.com/123456789' => 'https://player.vimeo.com/video/123456789',
            'https://video.contoh.test/embed/abc' => 'https://video.contoh.test/embed/abc',
        ];

        foreach ($kasus as $tautan => $harapan) {
            $item = new GalleryItem(['type' => 'video', 'video_url' => $tautan]);

            $this->assertSame($harapan, $item->embedUrl(), "URL embed salah untuk {$tautan}.");
        }

        $this->assertNull((new GalleryItem(['type' => 'video']))->embedUrl());
    }

    public function test_galeri_publik_merender_iframe_embed_bukan_tautan_tonton(): void
    {
        $this->seed();

        $item = GalleryItem::query()->where('type', 'video')->whereNotNull('video_url')->firstOrFail();

        $this->get(route('gallery.show', $item->gallery->slug))
            ->assertOk()
            ->assertSee($item->embedUrl(), false)
            ->assertDontSee($item->video_url, false);
    }
}
