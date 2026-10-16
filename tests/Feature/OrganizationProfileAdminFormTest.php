<?php

namespace Tests\Feature;

use App\Filament\Resources\OrganizationProfiles\OrganizationProfileResource;
use App\Filament\Resources\OrganizationProfiles\Pages\EditOrganizationProfile;
use App\Models\OrganizationProfile;
use App\Models\User;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tata letak formulir Profil Organisasi serta aturan data tunggal: tombol
 * "Buat Profil Organisasi" hanya tampil selama tabel masih kosong.
 */
class OrganizationProfileAdminFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        OrganizationProfile::flushCache();
    }

    /**
     * Seluruh isian yang dikelola lewat panel tetap tersedia setelah formulir
     * ditata ulang — masing-masing tepat satu kali.
     */
    public function test_seluruh_isian_profil_tetap_tersedia(): void
    {
        $nama = array_map(
            static fn (Field $field): string => $field->getName(),
            $this->semuaField($this->komponenForm()),
        );

        $diharapkan = [
            'name', 'abbreviation', 'tagline', 'founded_year', 'slogan',
            'vision', 'mission',
            'about_card_image', 'vision_card_image', 'mission_card_image',
            'short_description', 'description',
            'address', 'city', 'province', 'postal_code', 'email', 'phone',
            'whatsapp', 'map_embed',
            'facebook', 'instagram', 'youtube', 'twitter', 'tiktok',
        ];

        // Urutan bagian formulir tidak dipatok di sini; yang dijaga adalah
        // kelengkapan dan tidak ada isian yang berulang.
        sort($diharapkan);
        sort($nama);

        $this->assertSame($diharapkan, $nama, 'Daftar isian formulir Profil Organisasi berubah.');
    }

    /**
     * Formulir dikelompokkan menjadi lima bagian berjudul, dan setiap bagian
     * melintang penuh — bukan setengah lebar formulir seperti sebelumnya.
     */
    public function test_formulir_dikelompokkan_menjadi_lima_bagian_lebar_penuh(): void
    {
        $schema = OrganizationProfileResource::form(Schema::make());

        $judul = [];

        foreach ($schema->getComponents() as $component) {
            $this->assertInstanceOf(Section::class, $component);
            $this->assertSame('full', $component->getColumnSpan('default'));

            $judul[] = $component->getHeading();
        }

        $this->assertSame([
            'Identitas Organisasi',
            'Visi, Misi & Gambar Kartu Beranda',
            'Deskripsi',
            'Alamat & Kontak',
            'Media Sosial',
        ], $judul);

        // Lebar kolom ditetapkan formulir sendiri, sehingga halaman Ubah tidak
        // lagi menyuntikkan dua kolom bawaan Filament.
        $this->assertTrue($schema->hasCustomColumns());
        $this->assertSame(1, $schema->getColumns('lg'));
    }

    /**
     * Ketiga gambar kartu beranda disusun sejajar (satu grid tiga kolom) dengan
     * tinggi pratinjau seragam, sehingga tidak lagi tampil terjepit.
     */
    public function test_tiga_gambar_kartu_disusun_sejajar(): void
    {
        $grid = $this->gridTigaGambarKartu();

        $this->assertNotNull($grid, 'Ketiga gambar kartu beranda harus berada dalam satu grid.');
        $this->assertSame(3, $grid->getColumns('lg'));
        $this->assertSame('full', $grid->getColumnSpan('default'));

        $unggahan = array_values(array_filter(
            $this->semuaField([$grid]),
            static fn (Field $field): bool => $field instanceof FileUpload,
        ));

        $this->assertCount(3, $unggahan);

        foreach ($unggahan as $field) {
            $this->assertSame(
                '14rem',
                $field->getImagePreviewHeight(),
                "Tinggi pratinjau {$field->getName()} harus seragam.",
            );
        }
    }

    /**
     * Halaman sunting menampilkan seluruh judul bagian formulir.
     */
    public function test_halaman_sunting_menampilkan_judul_bagian(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $profil = OrganizationProfile::current();

        $response = $this->actingAs($admin)
            ->get("/admin/organization-profiles/{$profil->getKey()}/edit")
            ->assertOk();

        foreach ([
            'Identitas Organisasi',
            'Visi, Misi & Gambar Kartu Beranda',
            'Deskripsi',
            'Alamat & Kontak',
            'Media Sosial',
        ] as $judul) {
            $response->assertSee($judul);
        }
    }

    /**
     * Profil organisasi hanya satu baris: begitu datanya ada, tombol Buat hilang
     * dari halaman daftar dan halaman Buat menolak akses.
     */
    public function test_tombol_buat_disembunyikan_bila_profil_sudah_ada(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $profil = OrganizationProfile::current();

        $this->assertFalse(OrganizationProfileResource::canCreate());

        $this->actingAs($admin)
            ->get('/admin/organization-profiles')
            ->assertOk()
            ->assertSee($profil->name)
            ->assertDontSee('Buat Profil Organisasi');

        $this->actingAs($admin)
            ->get('/admin/organization-profiles/create')
            ->assertForbidden();
    }

    /**
     * Selama tabel masih kosong, pembuatan data tetap diizinkan — misalnya
     * setelah satu-satunya baris profil dihapus.
     *
     * Tidak diuji lewat HTTP karena panel admin sendiri memanggil
     * `OrganizationProfile::current()` (fallback nama & tagline pada
     * `PanelSetting::displayName()`), sehingga baris profil dibuat otomatis
     * begitu sebuah halaman panel dibuka.
     */
    public function test_akses_buat_tetap_tersedia_bila_belum_ada_data_profil(): void
    {
        $this->assertSame(0, OrganizationProfile::query()->count());

        $this->assertTrue(OrganizationProfileResource::getCreateAuthorizationResponse()->allowed());
        $this->assertTrue(OrganizationProfileResource::canCreate());

        OrganizationProfile::current();

        $this->assertFalse(OrganizationProfileResource::canCreate());
    }

    /**
     * Halaman sunting tetap dapat menyimpan seluruh isian setelah formulir
     * ditata ulang — termasuk Misi yang ditulis satu poin per baris.
     */
    public function test_halaman_sunting_dapat_menyimpan_perubahan(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $profil = OrganizationProfile::current();

        Livewire::actingAs($admin)
            ->test(EditOrganizationProfile::class, ['record' => $profil->getKey()])
            ->fillForm([
                'name' => 'Perwama Uji',
                'founded_year' => 2001,
                'mission' => "Poin satu\nPoin dua",
                'city' => 'Ambon',
                'whatsapp' => '0812-0000-0000',
                'map_embed' => '<iframe src="https://example.test"></iframe>',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $profil->refresh();

        $this->assertSame('Perwama Uji', $profil->name);
        $this->assertSame(2001, $profil->founded_year);
        $this->assertSame(['Poin satu', 'Poin dua'], $profil->mission);
        $this->assertSame('Ambon', $profil->city);
        $this->assertSame('0812-0000-0000', $profil->whatsapp);
        $this->assertSame('<iframe src="https://example.test"></iframe>', $profil->map_embed);
    }

    /**
     * @return array<int, SchemaComponent>
     */
    private function komponenForm(): array
    {
        return OrganizationProfileResource::form(Schema::make())->getComponents();
    }

    /**
     * Cari grid yang memuat ketiga unggahan gambar kartu beranda.
     */
    private function gridTigaGambarKartu(): ?Grid
    {
        $wajib = ['about_card_image', 'vision_card_image', 'mission_card_image'];

        foreach ($this->semuaKomponen($this->komponenForm()) as $component) {
            if (! $component instanceof Grid) {
                continue;
            }

            $nama = array_map(
                static fn (Field $field): string => $field->getName(),
                $this->semuaField([$component]),
            );

            if (array_diff($wajib, $nama) === []) {
                return $component;
            }
        }

        return null;
    }

    /**
     * Kumpulkan seluruh komponen (Section, Grid, dan isian) secara rekursif.
     *
     * @param  array<int, SchemaComponent>  $components
     * @return array<int, SchemaComponent>
     */
    private function semuaKomponen(array $components): array
    {
        $hasil = [];

        foreach ($components as $component) {
            if (! $component instanceof SchemaComponent) {
                continue;
            }

            $hasil[] = $component;
            $hasil = [...$hasil, ...$this->semuaKomponen($this->anakKomponen($component))];
        }

        return $hasil;
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

        foreach ($this->semuaKomponen($components) as $component) {
            if ($component instanceof Field) {
                $fields[] = $component;
            }
        }

        return $fields;
    }

    /**
     * Anak komponen dari sebuah Section/Grid.
     *
     * @return array<int, SchemaComponent>
     */
    private function anakKomponen(SchemaComponent $component): array
    {
        $children = $component->getDefaultChildComponents();

        if ($children instanceof Schema) {
            $children = $children->getComponents();
        }

        if (! is_array($children)) {
            return [];
        }

        return array_values(array_filter(
            $children,
            static fn ($child): bool => $child instanceof SchemaComponent,
        ));
    }
}
