<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
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
     * Statistik di atas daftar kartu memakai urutan Wilayah, DPD, Cabang,
     * Anggota dengan angka sesuai jenis organisasi masing-masing.
     */
    public function test_statistik_wilayah_memakai_dpd_dan_cabang(): void
    {
        // Region anak (kabupaten/kota) tidak dirender sebagai wilayah pada
        // halaman ini dan tidak boleh menambah angka "Wilayah".
        $province = Region::query()->where('slug', 'maluku')->firstOrFail();

        Region::query()->create([
            'parent_id' => $province->getKey(),
            'name' => 'Kota Uji Statistik',
            'slug' => 'kota-uji-statistik',
            'type' => 'city',
            'island' => 'Maluku',
            'is_active' => true,
        ]);

        $xpath = $this->xpath('/wilayah');

        $items = $xpath->query(
            '//section[.//p[normalize-space()="Wilayah"] and .//p[normalize-space()="Anggota"]]'
            .'//div[contains(concat(" ", normalize-space(@class), " "), " text-center ")]'
        );

        $this->assertNotFalse($items, 'Kueri statistik wilayah tidak valid.');
        $this->assertSame(4, $items->length, 'Statistik wilayah harus berisi empat angka.');

        $labels = [];
        $values = [];

        foreach ($items as $item) {
            $angka = $xpath->query('.//p[1]', $item)->item(0);
            $label = $xpath->query('.//p[2]', $item)->item(0);

            $this->assertNotNull($angka, 'Angka statistik wilayah tidak ditemukan.');
            $this->assertNotNull($label, 'Label statistik wilayah tidak ditemukan.');

            $values[] = trim($angka->textContent);
            $labels[] = trim($label->textContent);
        }

        $jumlahKelompok = Region::query()
            ->active()
            ->whereNull('parent_id')
            ->get()
            ->groupBy(fn (Region $region): string => $region->island ?: 'Lainnya')
            ->count();

        $this->assertSame(['Wilayah', 'DPD', 'Cabang', 'Anggota'], $labels);
        $this->assertSame([
            number_format($jumlahKelompok, 0, ',', '.'),
            number_format(Organization::query()->active()->where('type', 'dpw')->count(), 0, ',', '.'),
            number_format(Organization::query()->active()->where('type', 'pc')->count(), 0, ',', '.'),
            number_format(Member::query()->active()->count(), 0, ',', '.'),
        ], $values);
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
