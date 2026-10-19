<?php

namespace Tests\Feature;

use App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\EditGalleryItem;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\User;
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
 * Item galeri harus dapat diisi foto hasil unggahan dari komputer admin,
 * bukan hanya tautan. Data lama yang masih berupa tautan tetap harus utuh
 * ketika item disunting, agar perubahan formulir tidak menghapus foto yang
 * sudah tampil di situs publik.
 */
class GalleryItemPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->seed();
    }

    /**
     * Kolom Foto dan Gambar Mini pada formulir Buat/Ubah berupa unggahan
     * gambar pada disk `public`, bukan isian teks berisi tautan.
     */
    public function test_form_item_galeri_mengunggah_foto_dari_komputer(): void
    {
        // Schema dibangun lewat halaman Buat agar utilitas Get pada isian
        // bersyarat (visible/required) memiliki konteks Livewire.
        $form = Livewire::test(CreateGalleryItem::class)->instance()->getSchema('form');

        $unggahan = [];

        foreach ($this->semuaField($form->getComponents()) as $field) {
            if ($field instanceof FileUpload) {
                $unggahan[$field->getName()] = $field;
            }
        }

        foreach (['file' => 'Foto', 'thumbnail' => 'Gambar Mini'] as $nama => $label) {
            $this->assertArrayHasKey($nama, $unggahan, "Field {$nama} harus berupa unggahan gambar (FileUpload).");

            $this->assertSame('public', $unggahan[$nama]->getDiskName(), "Field {$nama} harus memakai disk publik.");
            $this->assertSame('uploads/gallery-items', $unggahan[$nama]->getDirectory());
            $this->assertContains('image/*', $unggahan[$nama]->getAcceptedFileTypes(), "Field {$nama} harus menerima berkas gambar.");
            $this->assertSame($label, $unggahan[$nama]->getLabel());
        }
    }

    /**
     * Foto yang dipilih dari komputer benar-benar tersimpan di disk publik
     * dan dirender pada halaman galeri publik.
     */
    public function test_foto_unggahan_tersimpan_di_disk_publik_dan_tampil_di_galeri(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $gallery = Gallery::query()->orderBy('id')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(CreateGalleryItem::class)
            ->fillForm([
                'gallery_id' => $gallery->id,
                'type' => 'photo',
                'file' => UploadedFile::fake()->image('kenangan-munas.jpg', 1200, 800),
                'caption' => 'Foto unggahan uji',
                'sort_order' => 99,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = GalleryItem::query()->where('caption', 'Foto unggahan uji')->firstOrFail();

        $this->assertIsString($item->file, 'Kolom Berkas harus berisi path berkas hasil unggahan.');
        $this->assertStringStartsWith('uploads/gallery-items/', $item->file);
        Storage::disk('public')->assertExists($item->file);

        $this->get('/galeri/'.$gallery->slug)
            ->assertOk()
            ->assertSee('storage/'.$item->file, false);
    }

    /**
     * Item lama yang fotonya masih berupa tautan tidak kehilangan foto saat
     * formulir Ubah dibuka lalu disimpan tanpa perubahan.
     */
    public function test_tautan_foto_lama_tetap_tersimpan_setelah_item_disunting(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $gallery = Gallery::query()->orderBy('id')->firstOrFail();

        $tautan = 'https://contoh.test/foto-lama.jpg';

        $item = GalleryItem::create([
            'gallery_id' => $gallery->id,
            'type' => 'photo',
            'file' => $tautan,
            'caption' => 'Foto tautan lama',
            'sort_order' => 90,
        ]);

        $this->actingAs($admin);

        Livewire::test(EditGalleryItem::class, ['record' => $item->getRouteKey()])
            ->assertSchemaStateSet(['file' => $tautan])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($tautan, $item->refresh()->file);
    }

    /**
     * Item berjenis video tidak diminta mengunggah foto; URL video tetap
     * menjadi isian utama.
     */
    public function test_item_video_tidak_meminta_unggahan_foto(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $gallery = Gallery::query()->orderBy('id')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(CreateGalleryItem::class)
            ->fillForm([
                'gallery_id' => $gallery->id,
                'type' => 'video',
                'video_url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4',
                'caption' => 'Video uji',
                'sort_order' => 98,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = GalleryItem::query()->where('caption', 'Video uji')->firstOrFail();

        $this->assertNull($item->file);
        $this->assertSame('https://www.youtube.com/watch?v=ScMzIvxBSi4', $item->video_url);
    }

    /**
     * Halaman Buat dan Ubah benar-benar terbuka dengan isian unggah foto,
     * termasuk saat item lama masih menyimpan tautan.
     */
    public function test_halaman_buat_dan_ubah_item_galeri_menampilkan_unggahan_foto(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $item = GalleryItem::query()->whereNotNull('file')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/gallery-items/create')
            ->assertOk()
            ->assertSee('Foto')
            ->assertSee('Gambar Mini');

        $this->actingAs($admin)
            ->get("/admin/gallery-items/{$item->getKey()}/edit")
            ->assertOk()
            ->assertSee('Foto')
            ->assertSee('Gambar Mini');
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
