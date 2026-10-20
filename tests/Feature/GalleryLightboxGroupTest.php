<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Gallery;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Foto dalam satu album harus berada pada grup lightbox yang sama agar
 * pengunjung dapat berpindah foto tanpa menutup pratinjau.
 */
class GalleryLightboxGroupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_foto_album_galeri_memakai_grup_lightbox_yang_sama(): void
    {
        $gallery = Gallery::query()
            ->whereHas('items', fn ($query) => $query->where('type', 'photo'))
            ->firstOrFail();

        $jumlahFoto = $gallery->items
            ->filter(fn ($item) => ! $item->isVideo() && $item->file)
            ->count();

        $this->assertGreaterThanOrEqual(2, $jumlahFoto, 'Album uji memerlukan minimal dua foto.');

        $response = $this->get(route('gallery.show', $gallery->slug))->assertOk();
        $tautan = $this->tautanLightbox($response->getContent());

        $this->assertCount($jumlahFoto, $tautan);

        foreach ($tautan as $link) {
            $this->assertSame(
                'gallery-'.$gallery->getKey(),
                $link->getAttribute('data-lightbox-group'),
                'Foto dalam satu album harus berbagi grup lightbox yang sama.',
            );
        }
    }

    public function test_foto_dokumentasi_kegiatan_memakai_grup_lightbox_album(): void
    {
        $activity = Activity::query()->whereNotNull('gallery_id')->firstOrFail();

        $response = $this->get(route('activity.show', $activity->slug))->assertOk();

        $this->assertNotEmpty($this->tautanLightbox($response->getContent()));
        $this->assertStringContainsString(
            'activity-gallery-'.$activity->gallery_id,
            $response->getContent(),
        );
    }

    /**
     * @return array<int, \DOMElement>
     */
    private function tautanLightbox(string $html): array
    {
        $dokumen = new DOMDocument;
        $sebelumnya = libxml_use_internal_errors(true);
        $dokumen->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($sebelumnya);

        $xpath = new DOMXPath($dokumen);
        $tautan = [];

        foreach ($xpath->query('//a[@data-lightbox]') as $link) {
            $tautan[] = $link;
        }

        return $tautan;
    }
}
