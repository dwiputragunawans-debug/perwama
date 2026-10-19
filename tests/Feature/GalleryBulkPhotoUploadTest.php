<?php

namespace Tests\Feature;

use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\EditGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\ListGalleryItems;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Menambah banyak foto ke satu album galeri dalam sekali unggah.
 *
 * Satu item galeri tetap mewakili satu foto (agar keterangan tiap foto dapat
 * disunting), tetapi admin tidak perlu membuat item satu per satu: aksi
 * "Unggah Beberapa Foto" membuatkan item untuk setiap berkas yang dipilih.
 */
class GalleryBulkPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->seed();
    }

    /**
     * Aksi unggah massal tersedia pada halaman Buat/Ubah Item Galeri, daftar
     * Item Galeri, dan Ubah Galeri.
     */
    public function test_aksi_unggah_beberapa_foto_tersedia_di_halaman_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $item = GalleryItem::query()->where('type', 'photo')->whereNotNull('file')->firstOrFail();
        $gallery = $item->gallery;

        $this->actingAs($admin);

        Livewire::test(CreateGalleryItem::class)
            ->assertActionExists('uploadPhotos');

        Livewire::test(EditGalleryItem::class, ['record' => $item->getRouteKey()])
            ->assertActionExists('uploadPhotos');

        Livewire::test(ListGalleryItems::class)
            ->assertActionExists('uploadPhotos');

        Livewire::test(EditGallery::class, ['record' => $gallery->getRouteKey()])
            ->assertActionExists('uploadPhotos');

        // Tombolnya benar-benar tampil di halaman yang dipakai admin.
        $this->get('/admin/gallery-items/create')
            ->assertOk()
            ->assertSee('Unggah Beberapa Foto');

        $this->get("/admin/gallery-items/{$item->getKey()}/edit")
            ->assertOk()
            ->assertSee('Unggah Beberapa Foto');

        $this->get("/admin/galleries/{$gallery->getKey()}/edit")
            ->assertOk()
            ->assertSee('Unggah Beberapa Foto');
    }

    /**
     * Tiga foto yang diunggah dari halaman Ubah Item menjadi tiga item foto
     * pada album yang sama, dengan urutan dan nomor keterangan yang menyambung.
     */
    public function test_beberapa_foto_sekaligus_menjadi_item_galeri_pada_album_yang_sama(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $item = GalleryItem::query()->where('type', 'photo')->whereNotNull('file')->firstOrFail();
        $gallery = $item->gallery;

        $jumlahFotoLama = $gallery->items()->where('type', 'photo')->count();
        $urutanTerakhir = (int) $gallery->items()->max('sort_order');
        $jumlahItemAwal = GalleryItem::query()->count();

        $this->actingAs($admin);

        Livewire::test(EditGalleryItem::class, ['record' => $item->getRouteKey()])
            ->callAction('uploadPhotos', data: [
                'caption' => 'Pelatihan Literasi Digital',
                'photos' => [
                    UploadedFile::fake()->image('dokumentasi-1.jpg', 1200, 800),
                    UploadedFile::fake()->image('dokumentasi-2.jpg', 1200, 800),
                    UploadedFile::fake()->image('dokumentasi-3.jpg', 1200, 800),
                ],
            ])
            ->assertHasNoActionErrors();

        $this->assertSame($jumlahItemAwal + 3, GalleryItem::query()->count());

        $baru = GalleryItem::query()
            ->where('gallery_id', $gallery->getKey())
            ->orderBy('sort_order')
            ->get()
            ->slice(-3)
            ->values();

        $this->assertSame(['photo', 'photo', 'photo'], $baru->pluck('type')->all());

        $this->assertSame([
            'Pelatihan Literasi Digital — '.($jumlahFotoLama + 1),
            'Pelatihan Literasi Digital — '.($jumlahFotoLama + 2),
            'Pelatihan Literasi Digital — '.($jumlahFotoLama + 3),
        ], $baru->pluck('caption')->all());

        $this->assertSame(
            [$urutanTerakhir + 1, $urutanTerakhir + 2, $urutanTerakhir + 3],
            $baru->pluck('sort_order')->map(fn (mixed $urutan): int => (int) $urutan)->all(),
        );

        foreach ($baru as $foto) {
            $this->assertIsString($foto->file);
            $this->assertStringStartsWith('uploads/gallery-items/', $foto->file);
            Storage::disk('public')->assertExists($foto->file);
        }

        // Foto baru langsung tampil pada halaman galeri publik.
        $this->get(route('gallery.show', $gallery->slug))
            ->assertOk()
            ->assertSee($baru->first()->file)
            ->assertSee('Pelatihan Literasi Digital — '.($jumlahFotoLama + 1));
    }

    /**
     * Aksi pada halaman daftar memakai album yang dipilih, dan tanpa
     * keterangan item baru tidak diberi keterangan palsu.
     */
    public function test_unggah_massal_dari_halaman_daftar_memakai_album_yang_dipilih(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $gallery = Gallery::query()->orderByDesc('id')->firstOrFail();

        $urutanTerakhir = (int) $gallery->items()->max('sort_order');

        $this->actingAs($admin);

        Livewire::test(ListGalleryItems::class)
            ->callAction('uploadPhotos', data: [
                'gallery_id' => $gallery->getKey(),
                'photos' => [
                    UploadedFile::fake()->image('kegiatan-1.jpg'),
                    UploadedFile::fake()->image('kegiatan-2.jpg'),
                ],
            ])
            ->assertHasNoActionErrors();

        $baru = GalleryItem::query()
            ->where('gallery_id', $gallery->getKey())
            ->orderBy('sort_order')
            ->get()
            ->slice(-2)
            ->values();

        $this->assertCount(2, $baru);
        $this->assertSame([null, null], $baru->pluck('caption')->all());
        $this->assertSame(
            [$urutanTerakhir + 1, $urutanTerakhir + 2],
            $baru->pluck('sort_order')->map(fn (mixed $urutan): int => (int) $urutan)->all(),
        );
    }

    /**
     * Tanpa memilih foto, aksi tidak boleh membuat item kosong.
     */
    public function test_aksi_unggah_massal_mewajibkan_minimal_satu_foto(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $item = GalleryItem::query()->where('type', 'photo')->whereNotNull('file')->firstOrFail();

        $jumlahItemAwal = GalleryItem::query()->count();

        $this->actingAs($admin);

        Livewire::test(EditGalleryItem::class, ['record' => $item->getRouteKey()])
            ->callAction('uploadPhotos', data: [
                'caption' => 'Tanpa foto',
            ])
            ->assertHasActionErrors(['photos']);

        $this->assertSame($jumlahItemAwal, GalleryItem::query()->count());
    }
}
