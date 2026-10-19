<?php

namespace Tests\Feature;

use App\Models\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deskripsi galeri diisi lewat RichEditor, jadi tersimpan sebagai HTML.
 * Halaman publik harus merendernya sebagai HTML, bukan menampilkan tag
 * `<p>` sebagai teks biasa.
 */
class GalleryDescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_deskripsi_galeri_dari_rich_editor_dirender_sebagai_html(): void
    {
        $gallery = Gallery::query()->firstOrFail();
        $gallery->update([
            'description' => '<p>Dokumentasi <strong>MUNAS VIII</strong> Tahun 2025.</p>',
        ]);

        $this->get(route('gallery.show', $gallery->slug))
            ->assertOk()
            ->assertSee('<p>Dokumentasi <strong>MUNAS VIII</strong> Tahun 2025.</p>', false)
            ->assertDontSee('&lt;p&gt;', false);
    }

    public function test_deskripsi_galeri_teks_biasa_tetap_tampil_sebagai_paragraf(): void
    {
        $gallery = Gallery::query()->firstOrFail();
        $gallery->update([
            'description' => "Dokumentasi MUNAS VIII Tahun 2025.\nDiselenggarakan di Bekasi.",
        ]);

        $this->get(route('gallery.show', $gallery->slug))
            ->assertOk()
            ->assertSee('Dokumentasi MUNAS VIII Tahun 2025.<br />', false)
            ->assertSee('Diselenggarakan di Bekasi.', false);
    }

    public function test_galeri_tanpa_deskripsi_tidak_menampilkan_blok_kosong(): void
    {
        $gallery = Gallery::query()->firstOrFail();
        $gallery->update(['description' => null]);

        $this->get(route('gallery.show', $gallery->slug))
            ->assertOk()
            ->assertDontSee('prose-perwama max-w-3xl', false);
    }
}
