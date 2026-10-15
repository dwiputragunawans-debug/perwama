<?php

namespace Tests\Feature;

use App\Filament\Resources\OrganizationProfiles\OrganizationProfileResource;
use App\Models\OrganizationProfile;
use App\Models\User;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationProfileCardImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        OrganizationProfile::flushCache();
    }

    /**
     * Pengaturan gambar ketiga kartu beranda berada pada satu bagian (Section)
     * bersama isian Visi & Misi — "satu dengan isi visi misi".
     */
    public function test_gambar_kartu_beranda_menyatu_dengan_visi_misi(): void
    {
        $namaField = $this->namaFieldDalamSatuSection('vision', 'mission');

        $this->assertNotNull(
            $namaField,
            'Isian Visi & Misi harus berada dalam satu bagian (Section).',
        );

        $this->assertContains('about_card_image', $namaField);
        $this->assertContains('vision_card_image', $namaField);
        $this->assertContains('mission_card_image', $namaField);
    }

    /**
     * Ketiga gambar kartu diunggah sebagai gambar pada disk publik.
     */
    public function test_gambar_kartu_beranda_berupa_unggahan_gambar(): void
    {
        $schema = OrganizationProfileResource::form(Schema::make());

        $unggahan = [];

        foreach ($this->semuaField($schema->getComponents()) as $field) {
            if ($field instanceof FileUpload) {
                $unggahan[] = $field->getName();
            }
        }

        foreach (['about_card_image', 'vision_card_image', 'mission_card_image'] as $nama) {
            $this->assertContains($nama, $unggahan, "Field {$nama} harus berupa unggahan gambar (FileUpload).");
        }
    }

    /**
     * Halaman sunting Profil Organisasi menampilkan bagian gambar kartu
     * beranda lengkap dengan label berbahasa Indonesia.
     */
    public function test_halaman_sunting_menampilkan_pengaturan_gambar_kartu(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $profil = OrganizationProfile::current();

        $this->actingAs($admin)
            ->get("/admin/organization-profiles/{$profil->getKey()}/edit")
            ->assertOk()
            ->assertSee('Visi, Misi & Gambar Kartu Beranda')
            ->assertSee('Gambar kartu Tentang Kami')
            ->assertSee('Gambar kartu Visi')
            ->assertSee('Gambar kartu Misi');
    }

    /**
     * Nama semua field di dalam satu Section yang memuat seluruh nama wajib.
     *
     * @return array<int, string>|null
     */
    private function namaFieldDalamSatuSection(string ...$wajib): ?array
    {
        $schema = OrganizationProfileResource::form(Schema::make());

        foreach ($schema->getComponents() as $component) {
            if (! $component instanceof Section) {
                continue;
            }

            $nama = array_map(
                static fn (Field $field): string => $field->getName(),
                $this->semuaField([$component]),
            );

            if (array_diff($wajib, $nama) === []) {
                return $nama;
            }
        }

        return null;
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
}
