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

    public function test_deskripsi_organisasi_mempertahankan_baris_dari_rich_editor(): void
    {
        $region = Region::query()->where('slug', 'dki-jakarta')->firstOrFail();
        $organization = $region->organizations()->firstOrFail();
        $organization->update([
            'description' => '<p>Ketua : Ibu Corry Martha Samallo - Siahainenia</p>'
                .'<p>Ketua I : Ibu Etty Parinusa</p>'
                .'<p>Bendahara : Ibu Atiek S. Patty</p>',
        ]);

        $this->get(route('region.show', $region->slug))
            ->assertOk()
            ->assertSee('Ketua : Ibu Corry Martha Samallo - Siahainenia<br />', false)
            ->assertSee('Ketua I : Ibu Etty Parinusa<br />', false)
            ->assertSee('Bendahara : Ibu Atiek S. Patty', false)
            ->assertDontSee('SiahaineniaKetua', false);
    }

    public function test_deskripsi_organisasi_yang_masih_menyimpan_entity_br_tidak_tampil_mentah(): void
    {
        $region = Region::query()->where('slug', 'dki-jakarta')->firstOrFail();
        $organization = $region->organizations()->firstOrFail();
        $organization->update([
            'description' => 'Ketua : Ibu Corry Martha Samallo - Siahainenia'
                .'&lt;br&gt;Ketua I : Ibu Etty Parinusa'
                .'&lt;br&gt;Bendahara : Ibu Atiek S. Patty',
        ]);

        $this->get(route('region.show', $region->slug))
            ->assertOk()
            ->assertSee('Siahainenia<br />', false)
            ->assertSee('Ketua I : Ibu Etty Parinusa<br />', false)
            ->assertDontSee('&amp;lt;br&amp;gt;', false);
    }

    public function test_deskripsi_organisasi_tidak_menampilkan_tag_html_sebagai_teks(): void
    {
        $region = Region::query()->where('slug', 'dki-jakarta')->firstOrFail();
        $organization = $region->organizations()->firstOrFail();
        $organization->update([
            'description' => '<p>Ketua : <strong>Ibu Corry</strong></p><p>Sekretaris : Ibu Suzette</p>',
        ]);

        $this->get(route('region.show', $region->slug))
            ->assertOk()
            ->assertSee('Ketua : Ibu Corry<br />', false)
            ->assertSee('Sekretaris : Ibu Suzette', false)
            ->assertDontSee('&lt;p&gt;', false)
            ->assertDontSee('&lt;strong&gt;', false);
    }
}
