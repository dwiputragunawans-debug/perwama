<?php

namespace Tests\Feature;

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman detail video harus memasang URL embed pada iframe, bukan tautan
 * halaman tonton YouTube yang akan ditolak dengan pesan "refused to connect".
 */
class VideoEmbedTest extends TestCase
{
    use RefreshDatabase;

    public function test_berbagai_tautan_youtube_dinormalkan_ke_url_embed(): void
    {
        $kasus = [
            'https://www.youtube.com/watch?v=ScMzIvxBSi4' => 'https://www.youtube.com/embed/ScMzIvxBSi4',
            'https://youtu.be/ScMzIvxBSi4?t=10' => 'https://www.youtube.com/embed/ScMzIvxBSi4',
            'https://www.youtube.com/embed/ScMzIvxBSi4' => 'https://www.youtube.com/embed/ScMzIvxBSi4',
            'https://www.youtube.com/shorts/ScMzIvxBSi4' => 'https://www.youtube.com/embed/ScMzIvxBSi4',
            'https://www.youtube.com/live/8HMNN7q0pDI?si=P2NSXe1Ubv8HoGnO' => 'https://www.youtube.com/embed/8HMNN7q0pDI',
            'https://vimeo.com/123456789' => 'https://player.vimeo.com/video/123456789',
            'https://video.contoh.test/embed/abc' => 'https://video.contoh.test/embed/abc',
        ];

        foreach ($kasus as $tautan => $harapan) {
            $video = new Video(['url' => $tautan]);

            $this->assertSame($harapan, $video->embedUrl(), "URL embed salah untuk {$tautan}.");
        }

        $this->assertNull((new Video)->embedUrl());
    }

    public function test_id_video_diambil_dari_tautan_live_untuk_gambar_mini(): void
    {
        $video = new Video(['url' => 'https://www.youtube.com/live/8HMNN7q0pDI?si=P2NSXe1Ubv8HoGnO']);

        $this->assertSame(
            'https://img.youtube.com/vi/8HMNN7q0pDI/hqdefault.jpg',
            $video->thumbnailUrl(),
        );
    }

    public function test_halaman_video_memakai_iframe_embed_untuk_tautan_live(): void
    {
        $video = Video::create([
            'title' => 'Munas PERWAMA INA',
            'description' => '<p>Dokumentasi Musyawarah Nasional.</p>',
            'url' => 'https://www.youtube.com/live/8HMNN7q0pDI?si=P2NSXe1Ubv8HoGnO',
            'category' => 'kegiatan',
            'is_featured' => false,
            'sort_order' => 1,
            'published_at' => now(),
        ]);

        $this->get(route('video.show', $video->slug))
            ->assertOk()
            ->assertSee('https://www.youtube.com/embed/8HMNN7q0pDI', false)
            ->assertDontSee('src="https://www.youtube.com/live/', false);
    }
}
