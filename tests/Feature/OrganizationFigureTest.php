<?php

namespace Tests\Feature;

use App\Filament\Resources\OrganizationFigures\Pages\CreateOrganizationFigure;
use App\Models\OrganizationFigure;
use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Bagian "Tokoh Organisasi" pada halaman Tentang Kami serta pengelolaannya
 * melalui panel admin. Setiap tokoh tampil sebagai kartu putih dengan foto
 * penuh selebar kartu pada bagian atas; urutan dan status aktifnya diatur dari
 * panel Tentang → Tokoh Organisasi.
 */
class OrganizationFigureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    /**
     * Hanya tokoh aktif yang tampil, mengikuti kolom Urutan. Empat kartu
     * disusun sejajar pada layar lebar dan masing-masing memakai bentuk kartu
     * situs (putih, sudut tumpul, bayangan halus).
     */
    public function test_bagian_tokoh_menampilkan_empat_kartu_aktif_sesuai_urutan(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/organization-figures/robine.jpg', 'berkas-uji');
        Storage::disk('public')->put('uploads/organization-figures/johanna.jpg', 'berkas-uji');

        foreach ([
            ['Robine Inka Caecilia M. P.', 1, 'uploads/organization-figures/robine.jpg', true],
            ['Johanna Pietersz', 2, 'uploads/organization-figures/johanna.jpg', true],
            ['Evie Poetiray', 3, null, true],
            ['Petrosina Jacoba (Dee) Tuwaahatu - Amahorseja', 4, null, true],
            ['Tokoh Tidak Aktif', 0, null, false],
        ] as [$name, $sortOrder, $photo, $isActive]) {
            OrganizationFigure::create([
                'name' => $name,
                'position' => 'Pendiri',
                'photo' => $photo,
                'sort_order' => $sortOrder,
                'is_active' => $isActive,
            ]);
        }

        $xpath = $this->xpath('/tentang');
        $section = $xpath->query('//*[@id="tokoh-organisasi"]')->item(0);

        $this->assertInstanceOf(DOMElement::class, $section, 'Bagian Tokoh Organisasi tidak ditemukan.');

        $judul = $xpath->query('.//h2', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $judul);
        $this->assertSame('Tokoh Organisasi', trim($judul->textContent));
        $this->assertStringContainsString('section-title', $judul->getAttribute('class'));

        // Grid empat kolom pada layar lebar, dua kolom pada tablet.
        $grid = $xpath->query('.//div[contains(@class, "lg:grid-cols-4")]', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $grid, 'Empat kartu harus tersusun sejajar pada layar lebar.');
        $this->assertStringContainsString('sm:grid-cols-2', $grid->getAttribute('class'));

        $kartu = $xpath->query('.//article[contains(concat(" ", normalize-space(@class), " "), " figure-card ")]', $section);

        $this->assertCount(4, $kartu, 'Hanya empat tokoh aktif yang boleh tampil.');
        $this->assertSame(
            ['Robine Inka Caecilia M. P.', 'Johanna Pietersz', 'Evie Poetiray', 'Petrosina Jacoba (Dee) Tuwaahatu - Amahorseja'],
            $this->teks($xpath, './/h3[contains(@class, "figure-card-name")]', $section),
        );
        $this->assertSame(
            ['Pendiri', 'Pendiri', 'Pendiri', 'Pendiri'],
            $this->teks($xpath, './/*[contains(@class, "figure-card-role")]', $section),
        );
        $this->assertStringNotContainsString('Tokoh Tidak Aktif', $section->textContent);

        // Setiap kartu memakai kelas .card agar warnanya sama dengan kartu lain.
        foreach ($kartu as $item) {
            $this->assertInstanceOf(DOMElement::class, $item);
            $this->assertStringContainsString('card', $item->getAttribute('class'));

            // Foto dan teks berada dalam dua bagian terpisah: foto full-bleed
            // di atas, lalu nama/jabatan di area teks di bawahnya.
            $this->assertSame(1, $xpath->query('.//div[contains(@class, "figure-card-body")]', $item)->length);
        }

        // Foto diunggah lewat disk publik dan dapat diperbesar (lightbox).
        $foto = $xpath->query('.//a[contains(@class, "figure-card-photo")]', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $foto);
        $this->assertStringContainsString('storage/uploads/organization-figures/robine.jpg', $foto->getAttribute('href'));
        $this->assertSame(
            'Perbesar foto Robine Inka Caecilia M. P.',
            $foto->getAttribute('aria-label'),
        );
        $this->assertSame(1, $foto->getElementsByTagName('img')->length, 'Foto harus menjadi isi langsung area foto.');
    }

    /**
     * Tokoh yang belum memiliki foto tetap tampil dengan bidang inisial,
     * sehingga kartu tidak kosong sebelum pengelola mengunggah fotonya.
     */
    public function test_tokoh_tanpa_foto_menampilkan_inisial_nama(): void
    {
        OrganizationFigure::create([
            'name' => 'Evie Poetiray',
            'position' => 'Pendiri',
            'photo' => null,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $xpath = $this->xpath('/tentang');
        $section = $xpath->query('//*[@id="tokoh-organisasi"]')->item(0);

        $this->assertInstanceOf(DOMElement::class, $section);

        $inisial = $xpath->query('.//*[contains(@class, "figure-card-initial")]', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $inisial);
        $this->assertSame('E', trim($inisial->textContent));
        $this->assertSame(0, $xpath->query('.//img', $section)->length, 'Tanpa foto tidak boleh ada <img> kosong.');
    }

    /**
     * Bila seluruh tokoh dinonaktifkan (atau belum ada data), bagian beserta
     * judulnya tidak dirender sehingga halaman tidak menampilkan area kosong.
     */
    public function test_bagian_tokoh_tidak_tampil_bila_tidak_ada_tokoh_aktif(): void
    {
        OrganizationFigure::create([
            'name' => 'Tokoh Tidak Aktif',
            'position' => 'Pendiri',
            'sort_order' => 1,
            'is_active' => false,
        ]);

        $response = $this->get('/tentang')->assertOk();

        $response->assertDontSee('id="tokoh-organisasi"', false);
        $response->assertDontSee('Tokoh Tidak Aktif');
        $response->assertDontSee('Tokoh Organisasi');
    }

    /**
     * Foto yang dipilih admin dari komputer tersimpan pada disk `public` dan
     * langsung tampil di halaman Tentang.
     */
    public function test_admin_dapat_mengunggah_foto_tokoh_dan_foto_tampil_di_situs(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        Livewire::test(CreateOrganizationFigure::class)
            ->fillForm([
                'name' => 'Robine Inka Caecilia M. P.',
                'position' => 'Pendiri',
                'photo' => UploadedFile::fake()->image('robine.jpg', 600, 600),
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $tokoh = OrganizationFigure::query()->where('name', 'Robine Inka Caecilia M. P.')->firstOrFail();

        $this->assertIsString($tokoh->photo);
        $this->assertStringStartsWith('uploads/organization-figures/', $tokoh->photo);
        Storage::disk('public')->assertExists($tokoh->photo);

        $this->get('/tentang')
            ->assertOk()
            ->assertSee('Robine Inka Caecilia M. P.')
            ->assertSee('storage/'.$tokoh->photo, false);
    }

    /**
     * Menu "Tokoh Organisasi" tersedia pada grup Tentang dan halaman daftar
     * serta formulirnya dapat dibuka oleh admin.
     */
    public function test_menu_admin_tokoh_organisasi_dapat_dibuka(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        OrganizationFigure::create([
            'name' => 'Johanna Pietersz',
            'position' => 'Pendiri',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/organization-figures')
            ->assertOk()
            ->assertSee('Tokoh Organisasi')
            ->assertSee('Johanna Pietersz');

        $this->actingAs($admin)
            ->get('/admin/organization-figures/create')
            ->assertOk()
            ->assertSee('Nama Tokoh')
            ->assertSee('Foto');
    }

    private function xpath(string $url): DOMXPath
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get($url)->assertOk()->getContent());
        libxml_clear_errors();

        return new DOMXPath($dom);
    }

    /**
     * Teks seluruh elemen yang cocok, dalam urutan kemunculan di halaman.
     *
     * @return array<int, string>
     */
    private function teks(DOMXPath $xpath, string $query, ?DOMElement $konteks = null): array
    {
        $hasil = [];

        foreach ($xpath->query($query, $konteks) as $node) {
            $hasil[] = trim($node->textContent);
        }

        return $hasil;
    }
}
