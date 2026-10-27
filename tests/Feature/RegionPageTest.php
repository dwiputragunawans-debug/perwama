<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\Region;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman publik "Wilayah" memakai pembagian yang sama dengan halaman detail:
 * DPD dihitung sebagai "DPD" dan PC sebagai "cabang". Baris ranting pada tabel
 * branches tidak ikut dihitung.
 */
class RegionPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * Jumlah "DPD" pada kartu wilayah hanya menghitung organisasi tipe dpw,
     * bukan DPD + PC.
     */
    public function test_kartu_wilayah_menghitung_dpd_saja(): void
    {
        $region = Region::query()->where('slug', 'maluku')->firstOrFail();

        Organization::query()->create([
            'region_id' => $region->getKey(),
            'name' => 'PC Uji Maluku',
            'slug' => 'pc-uji-maluku',
            'type' => 'pc',
            'member_count' => 0,
            'sort_order' => 100,
            'is_active' => true,
        ]);

        $xpath = $this->xpath('/wilayah');

        $cards = $xpath->query('//article[.//h3/a[normalize-space()="Maluku"]]');
        $this->assertNotFalse($cards, 'Kueri kartu wilayah tidak valid.');

        $card = $cards->item(0);
        $this->assertNotNull($card, 'Kartu wilayah Maluku tidak ditemukan.');

        $text = preg_replace('/\s+/u', ' ', $card->textContent);

        $this->assertStringContainsString('1 DPD', $text);
        $this->assertStringNotContainsString('2 DPD', $text);
    }

    /**
     * Jumlah "cabang" pada kartu mengikuti organisasi bertipe PC, bukan baris
     * pada tabel branches (ranting), agar sama dengan daftar di halaman detail.
     */
    public function test_kartu_wilayah_menghitung_cabang_dari_organisasi_pc(): void
    {
        $region = Region::query()->where('slug', 'maluku')->firstOrFail();

        $dpw = Organization::query()
            ->where('region_id', $region->getKey())
            ->where('type', 'dpw')
            ->firstOrFail();

        Organization::query()->create([
            'region_id' => $region->getKey(),
            'parent_id' => $dpw->getKey(),
            'name' => 'PC Uji Maluku',
            'slug' => 'pc-uji-maluku',
            'type' => 'pc',
            'member_count' => 0,
            'sort_order' => 100,
            'is_active' => true,
        ]);

        // Ranting pada tabel branches tidak ditampilkan di halaman detail,
        // sehingga tidak boleh menambah angka "cabang" pada kartu.
        foreach (['Ranting Uji Maluku 1', 'Ranting Uji Maluku 2'] as $index => $name) {
            Branch::query()->create([
                'organization_id' => $dpw->getKey(),
                'region_id' => $region->getKey(),
                'name' => $name,
                'slug' => 'ranting-uji-maluku-'.($index + 1),
                'type' => 'ranting',
                'member_count' => 0,
                'is_active' => true,
            ]);
        }

        $xpath = $this->xpath('/wilayah');

        $cards = $xpath->query('//article[.//h3/a[normalize-space()="Maluku"]]');
        $this->assertNotFalse($cards, 'Kueri kartu wilayah tidak valid.');

        $card = $cards->item(0);
        $this->assertNotNull($card, 'Kartu wilayah Maluku tidak ditemukan.');

        $text = preg_replace('/\s+/u', ' ', $card->textContent);

        $this->assertStringContainsString('1 DPD', $text);
        $this->assertStringContainsString('1 cabang', $text);
        $this->assertStringNotContainsString('2 cabang', $text);
    }

    /**
     * Organisasi selain PC tampil di bagian "Organisasi", sedangkan PC tampil
     * di bagian "Cabang".
     */
    public function test_detail_wilayah_menampilkan_pimpinan_cabang_di_bawah_judul_cabang(): void
    {
        $region = Region::query()->where('slug', 'maluku')->firstOrFail();

        Organization::query()->create([
            'region_id' => $region->getKey(),
            'name' => 'PC Uji Maluku',
            'slug' => 'pc-uji-maluku',
            'type' => 'pc',
            'member_count' => 0,
            'sort_order' => 100,
            'is_active' => true,
        ]);

        $xpath = $this->xpath('/wilayah/'.$region->slug);

        $headings = $xpath->query('//h2[normalize-space()="Organisasi" or normalize-space()="Cabang"]');
        $this->assertNotFalse($headings, 'Kueri judul bagian organisasi tidak valid.');

        $judul = [];

        foreach ($headings as $heading) {
            $judul[] = trim($heading->textContent);
        }

        $this->assertSame(['Organisasi', 'Cabang'], $judul);

        // Kedua kelompok diberi jarak vertikal yang jelas (lebih besar daripada
        // jarak judul ke kartu) agar judul "Cabang" tidak menempel pada kartu DPD.
        $daftarKelompok = $xpath->query('//h2[normalize-space()="Cabang"]/parent::div/parent::div')->item(0);
        $this->assertNotNull($daftarKelompok, 'Wadah kelompok organisasi tidak ditemukan.');
        $this->assertStringContainsString('space-y-16', $daftarKelompok->getAttribute('class'));

        $organisasiPertama = $xpath->query('//h2[normalize-space()="Organisasi"]/following::h3[1]');
        $this->assertNotFalse($organisasiPertama, 'Kueri organisasi pertama tidak valid.');
        $this->assertNotNull($organisasiPertama->item(0), 'Organisasi DPD tidak ditemukan.');
        $this->assertSame('Dewan Pengurus Wilayah Maluku', trim($organisasiPertama->item(0)->textContent));

        $cabangPertama = $xpath->query('//h2[normalize-space()="Cabang"]/following::h3[1]');
        $this->assertNotFalse($cabangPertama, 'Kueri cabang pertama tidak valid.');
        $this->assertNotNull($cabangPertama->item(0), 'Pimpinan cabang tidak ditemukan.');
        $this->assertSame('PC Uji Maluku', trim($cabangPertama->item(0)->textContent));
    }

    private function xpath(string $url): DOMXPath
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get($url)->assertOk()->getContent());
        libxml_clear_errors();

        return new DOMXPath($dom);
    }
}
