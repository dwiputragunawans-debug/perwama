<?php

namespace Tests\Feature;

use App\Filament\Resources\OrganizationIdentities\OrganizationIdentityResource;
use App\Filament\Resources\OrganizationIdentities\Pages\EditOrganizationIdentity;
use App\Models\OrganizationIdentity;
use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Bagian "Identitas Organisasi" pada halaman Tentang Kami serta pengaturannya
 * di panel admin. Isinya berupa judul, lirik Mars PERWAMA INA, dan gambar
 * pendukung (mis. partitur) yang disimpan sebagai satu baris data (singleton).
 */
class OrganizationIdentityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Model memakai cache statis; dibersihkan agar tiap uji berdiri sendiri.
        OrganizationIdentity::flushCache();
    }

    /**
     * Lirik bawaan tersedia setelah migrasi sehingga bagian langsung tayang.
     */
    public function test_lirik_bawaan_mars_tersedia(): void
    {
        $identity = OrganizationIdentity::current();

        $this->assertSame('Mars PERWAMA INA', $identity->title);
        $this->assertSame('Ciptaan: Izzak Bakar', $identity->credit());
        $this->assertCount(4, $identity->verses(), 'Lirik bawaan terdiri atas dua bait, refrein, dan bait penutup.');
        $this->assertStringContainsString('Menolong sesama manusia', $identity->verses()[0]);
        $this->assertStringContainsString('Melayani Tuhan dan sesamanya', $identity->verses()[3]);
        $this->assertNull($identity->images, 'Baris bawaan belum memiliki gambar pendukung.');
        $this->assertTrue($identity->imageUrls()->isEmpty());
    }

    /**
     * Deskripsi dipecah menjadi bait pada baris kosong, sedangkan pemenggalan
     * baris di dalam satu bait tetap dipertahankan. Baris "Ciptaan:" dipisahkan
     * sebagai kredit agar tidak ikut ditata sebagai lirik.
     */
    public function test_deskripsi_dipecah_menjadi_bait_dan_kredit(): void
    {
        $identity = OrganizationIdentity::current();

        $identity->update([
            'description' => "Ciptaan: Pencipta Uji\r\n\r\nBait pertama\nBaris kedua\r\n\r\nRef. (2x)\r\n\r\nMajulah bersama",
        ]);

        $this->assertSame('Ciptaan: Pencipta Uji', $identity->credit());
        $this->assertSame(
            ["Bait pertama\nBaris kedua", 'Ref. (2x)', 'Majulah bersama'],
            $identity->verses(),
        );
    }

    /**
     * Bagian tampil setelah CTA, memakai latar dan warna bagian Sejarah, serta
     * membatasi lebar isi 896px dengan teks rata tengah yang membungkus sendiri.
     */
    public function test_bagian_identitas_tampil_setelah_cta_dengan_tata_letak_yang_diinginkan(): void
    {
        $identity = OrganizationIdentity::current();
        $identity->update([
            'title' => 'Mars Uji PERWAMA',
            'description' => "Ciptaan: Pencipta Uji\n\nBait pertama\nBaris kedua\n\nBait kedua",
        ]);
        OrganizationIdentity::flushCache();

        $xpath = $this->xpath('/tentang');
        $section = $xpath->query('//*[@id="identitas-organisasi"]')->item(0);

        $this->assertInstanceOf(DOMElement::class, $section, 'Bagian Identitas Organisasi tidak ditemukan.');

        $kelas = $section->getAttribute('class');

        $this->assertStringContainsString('section-soft', $kelas, 'Latar bagian harus sama dengan bagian Sejarah.');
        $this->assertStringContainsString('section-y', $kelas);

        // Label dan judul memakai gaya yang sama dengan bagian Sejarah.
        $this->assertSame(1, $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " section-eyebrow ")]', $section)->length);

        $judul = $xpath->query('.//h2', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $judul);
        $this->assertSame('Mars Uji PERWAMA', trim($judul->textContent));
        $this->assertStringContainsString('section-title', $judul->getAttribute('class'));

        // Lebar isi 896px (max-w-4xl) dan rata tengah.
        $pembungkus = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " max-w-4xl ")]', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $pembungkus, 'Isi bagian harus dibatasi sekitar 800–900px.');
        $this->assertStringContainsString('text-center', $pembungkus->getAttribute('class'));

        // Kredit pencipta tampil terpisah dengan warna meta.
        $kredit = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " text-meta ")]', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $kredit);
        $this->assertSame('Ciptaan: Pencipta Uji', trim($kredit->textContent));

        // Dua bait tampil sebagai paragraf terpisah; baris di dalam bait
        // dipertahankan lewat whitespace-pre-line.
        $bait = $xpath->query('.//p[contains(concat(" ", normalize-space(@class), " "), " whitespace-pre-line ")]', $section);

        $this->assertSame(2, $bait->length);
        $this->assertSame("Bait pertama\nBaris kedua", trim($bait->item(0)->textContent));

        // Bagian ini adalah section terakhir di <main>, tepat sebelum footer,
        // dan didahului CTA "Ingin tahu lebih banyak?".
        $semuaBagian = $xpath->query('//main//section');
        $terakhir = $semuaBagian->item($semuaBagian->length - 1);

        $this->assertSame($section, $terakhir, 'Bagian Identitas Organisasi harus menjadi bagian terakhir sebelum footer.');

        $sebelumnya = $semuaBagian->item($semuaBagian->length - 2);

        $this->assertInstanceOf(DOMElement::class, $sebelumnya);
        $this->assertStringContainsString('Ingin tahu lebih banyak?', $sebelumnya->textContent);
    }

    /**
     * Gambar pendukung tampil di bawah lirik, mempertahankan rasio aslinya,
     * dan membuka lightbox situs dengan grup yang sama agar dapat berpindah
     * gambar tanpa menutup pratinjau.
     */
    public function test_gambar_pendukung_tampil_di_bawah_lirik_dengan_lightbox(): void
    {
        $identity = OrganizationIdentity::current();
        $identity->update([
            'description' => 'Bait pertama',
            'images' => [
                'uploads/organization-identities/partitur-1.jpg',
                'uploads/organization-identities/partitur-2.jpg',
            ],
        ]);
        OrganizationIdentity::flushCache();

        $xpath = $this->xpath('/tentang');
        $section = $xpath->query('//*[@id="identitas-organisasi"]')->item(0);

        $this->assertInstanceOf(DOMElement::class, $section);

        // Galeri berada pada wadah yang lebih lebar daripada teks lirik.
        $galeri = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " max-w-5xl ")]', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $galeri, 'Gambar pendukung harus berada di bawah lirik.');

        $tautan = $xpath->query('.//a[@data-lightbox]', $section);

        $this->assertCount(2, $tautan);

        foreach ($tautan as $link) {
            $this->assertSame('identitas-organisasi', $link->getAttribute('data-lightbox-group'));
            $this->assertStringContainsString('storage/uploads/organization-identities/', $link->getAttribute('href'));
        }

        $gambar = $xpath->query('.//figure//img', $section);

        $this->assertCount(2, $gambar);

        foreach ($gambar as $foto) {
            $this->assertStringContainsString('h-auto', $foto->getAttribute('class'));
            $this->assertStringNotContainsString('object-cover', $foto->getAttribute('class'), 'Partitur tidak boleh dipotong.');
        }

        // Paragraf lirik muncul lebih dahulu dalam dokumen, baru galeri gambar.
        $lirik = $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " space-y-8 ")]', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $lirik);
        $this->assertNotSame(
            0,
            $lirik->compareDocumentPosition($tautan->item(0)) & DOMNode::DOCUMENT_POSITION_FOLLOWING,
            'Galeri harus dirender setelah lirik.',
        );
    }

    /**
     * Bila lirik dikosongkan tetapi gambar pendukung tersedia, bagian tetap
     * tampil agar partitur tidak ikut hilang dari halaman publik.
     */
    public function test_bagian_tetap_tampil_bila_hanya_ada_gambar(): void
    {
        OrganizationIdentity::current()->update([
            'description' => null,
            'images' => ['uploads/organization-identities/partitur.jpg'],
        ]);
        OrganizationIdentity::flushCache();

        $this->get('/tentang')
            ->assertOk()
            ->assertSee('id="identitas-organisasi"', false)
            ->assertSee('storage/uploads/organization-identities/partitur.jpg', false);
    }

    /**
     * Tanpa deskripsi maupun gambar, bagian tidak dirender agar tidak
     * menyisakan ruang kosong.
     */
    public function test_bagian_identitas_tidak_tampil_bila_deskripsi_dan_gambar_kosong(): void
    {
        OrganizationIdentity::current()->update(['description' => null, 'images' => []]);
        OrganizationIdentity::flushCache();

        $this->get('/tentang')
            ->assertOk()
            ->assertDontSee('id="identitas-organisasi"', false);
    }

    /**
     * Menu admin "Identitas Organisasi" tersedia pada grup Tentang, hanya
     * memiliki satu record, dan menyediakan isian Judul, Deskripsi, serta
     * unggahan gambar pendukung.
     */
    public function test_menu_admin_identitas_organisasi_tersedia_dan_bersifat_tunggal(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertSame('Identitas Organisasi', OrganizationIdentityResource::getNavigationLabel());
        $this->assertSame('Tentang', OrganizationIdentityResource::getNavigationGroup());
        $this->assertFalse(OrganizationIdentityResource::canCreate());

        $this->actingAs($admin)->get('/admin/organization-identities')->assertOk();

        $this->actingAs($admin)
            ->get('/admin/organization-identities/1/edit')
            ->assertOk()
            ->assertSee('Identitas Organisasi', false)
            ->assertSee('title', false)
            ->assertSee('description', false)
            ->assertSee('Gambar Pendukung', false)
            ->assertSee('images', false)
            ->assertSee('Mars PERWAMA INA', false);

        OrganizationIdentity::current();
        OrganizationIdentity::current();

        $this->assertSame(1, OrganizationIdentity::query()->count());
    }

    /**
     * Form admin menyediakan satu isian unggahan yang menerima banyak gambar,
     * dapat diurutkan ulang, dan disimpan pada disk publik.
     */
    public function test_form_admin_menerima_lebih_dari_satu_gambar_pendukung(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = User::factory()->create(['is_admin' => true]);
        $identity = OrganizationIdentity::current();

        $this->actingAs($admin);

        $form = Livewire::test(EditOrganizationIdentity::class, ['record' => $identity->getRouteKey()])
            ->instance()
            ->getSchema('form');

        $unggahan = null;

        foreach ($this->semuaField($form->getComponents()) as $field) {
            if ($field instanceof FileUpload && $field->getName() === 'images') {
                $unggahan = $field;
                break;
            }
        }

        $this->assertInstanceOf(FileUpload::class, $unggahan, 'Field images harus berupa unggahan gambar (FileUpload).');
        $this->assertTrue($unggahan->isMultiple(), 'Admin harus dapat memilih lebih dari satu gambar.');
        $this->assertTrue($unggahan->isReorderable(), 'Urutan gambar harus dapat diatur.');
        $this->assertSame('public', $unggahan->getDiskName());
        $this->assertSame('uploads/organization-identities', $unggahan->getDirectory());
        $this->assertSame(20, $unggahan->getMaxFiles());
        $this->assertContains('image/*', $unggahan->getAcceptedFileTypes());
    }

    /**
     * Beberapa gambar yang dipilih dari komputer benar-benar tersimpan pada
     * disk publik, urutannya dipertahankan, dan tampil pada halaman Tentang.
     */
    public function test_admin_dapat_mengunggah_beberapa_gambar_pendukung(): void
    {
        Storage::fake('public');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = User::factory()->create(['is_admin' => true]);
        $identity = OrganizationIdentity::current();

        $this->actingAs($admin);

        Livewire::test(EditOrganizationIdentity::class, ['record' => $identity->getRouteKey()])
            ->fillForm([
                'images' => [
                    UploadedFile::fake()->image('partitur-1.jpg', 900, 1200),
                    UploadedFile::fake()->image('partitur-2.jpg', 900, 1200),
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $identity->refresh();

        $this->assertIsArray($identity->images);
        $this->assertCount(2, $identity->images);

        $response = $this->get('/tentang')->assertOk();

        foreach ($identity->images as $path) {
            $this->assertStringStartsWith('uploads/organization-identities/', $path);
            Storage::disk('public')->assertExists($path);
            $response->assertSee('storage/'.$path, false);
        }
    }

    /**
     * Kumpulkan seluruh komponen bertipe Field secara rekursif.
     *
     * @param  array<int, SchemaComponent>  $components
     * @return array<int, Field>
     */
    private function semuaField(array $components): array
    {
        $fields = [];

        foreach ($components as $component) {
            if ($component instanceof Field) {
                $fields[] = $component;
            }

            $children = $component->getDefaultChildComponents();

            if ($children instanceof Schema) {
                $children = $children->getComponents();
            }

            foreach ($children as $child) {
                if ($child instanceof SchemaComponent) {
                    $fields = [...$fields, ...$this->semuaField([$child])];
                }
            }
        }

        return $fields;
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
