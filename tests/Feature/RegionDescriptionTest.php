<?php

namespace Tests\Feature;

use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deskripsi wilayah diisi lewat RichEditor, jadi tersimpan sebagai HTML.
 * Halaman publik harus merendernya sebagai HTML, bukan menampilkan tag
 * `<p>` sebagai teks biasa.
 */
class RegionDescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_deskripsi_wilayah_dari_rich_editor_dirender_sebagai_html(): void
    {
        $region = Region::query()->where('slug', 'dki-jakarta')->firstOrFail();
        $region->update([
            'description' => '<p>Pusat <strong>pemerintahan nasional</strong> dan kantor Sekretariat Nasional PERWAMA INA.</p>',
        ]);

        $this->get(route('region.show', $region->slug))
            ->assertOk()
            ->assertSee('<p>Pusat <strong>pemerintahan nasional</strong> dan kantor Sekretariat Nasional PERWAMA INA.</p>', false)
            ->assertDontSee('&lt;p&gt;', false);
    }

    public function test_deskripsi_wilayah_teks_biasa_tetap_tampil_sebagai_paragraf(): void
    {
        $region = Region::query()->where('slug', 'dki-jakarta')->firstOrFail();
        $region->update([
            'description' => "Pusat pemerintahan nasional.\nKantor Sekretariat Nasional PERWAMA INA.",
        ]);

        $this->get(route('region.show', $region->slug))
            ->assertOk()
            ->assertSee('Pusat pemerintahan nasional.<br />', false)
            ->assertSee('Kantor Sekretariat Nasional PERWAMA INA.', false);
    }
}
