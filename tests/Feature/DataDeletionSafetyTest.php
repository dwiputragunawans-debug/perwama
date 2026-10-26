<?php

namespace Tests\Feature;

use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Filament\Resources\Galleries\Pages\ListGalleries;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\News;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;
use App\Support\ForeignKeyConstraint;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Penghapusan data tidak boleh berantai ke tabel anak. Foreign key yang masih
 * dipakai harus menolak penghapusan, dan panel admin menampilkan pesan yang
 * dapat dipahami alih-alih halaman error.
 */
class DataDeletionSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_migration_tidak_lagi_memakai_cascade_delete(): void
    {
        foreach (glob(database_path('migrations/*.php')) as $file) {
            $isi = file_get_contents($file);

            $this->assertStringNotContainsString('cascadeOnDelete', $isi, basename($file));
            $this->assertStringNotContainsString("onDelete('cascade')", $isi, basename($file));
        }
    }

    public function test_soft_delete_galeri_tidak_ikut_menghapus_item_dan_force_delete_tetap_ditolak(): void
    {
        $gallery = $this->galeriDenganItem();

        $this->assertTrue($gallery->delete());

        $this->assertSoftDeleted('galleries', ['id' => $gallery->getKey()]);
        $this->assertDatabaseHas('gallery_items', [
            'gallery_id' => $gallery->getKey(),
            'deleted_at' => null,
        ]);

        try {
            $gallery->forceDelete();
            $this->fail('Hapus permanen galeri yang masih memiliki item seharusnya ditolak foreign key.');
        } catch (QueryException $exception) {
            $this->assertTrue(ForeignKeyConstraint::violated($exception));
        }

        $this->assertDatabaseHas('galleries', ['id' => $gallery->getKey()]);
        $this->assertDatabaseHas('gallery_items', ['gallery_id' => $gallery->getKey()]);
    }

    public function test_tag_yang_masih_dipakai_konten_tidak_dapat_dihapus(): void
    {
        $news = News::query()->create([
            'title' => 'Berita dengan tag',
            'slug' => 'berita-dengan-tag',
        ]);

        $tag = Tag::query()->create([
            'name' => 'Perempuan',
            'slug' => 'perempuan',
        ]);

        $news->tags()->attach($tag);

        try {
            $tag->delete();
            $this->fail('Tag yang masih dipakai seharusnya ditolak foreign key.');
        } catch (QueryException $exception) {
            $this->assertTrue(ForeignKeyConstraint::violated($exception));
        }

        $this->assertDatabaseHas('tags', ['id' => $tag->getKey()]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->getKey(),
            'taggable_id' => $news->getKey(),
            'taggable_type' => $news->getMorphClass(),
        ]);
    }

    public function test_relasi_null_on_delete_tetap_mempertahankan_data_anak(): void
    {
        $region = Region::query()->create([
            'name' => 'Maluku',
            'slug' => 'maluku',
            'type' => 'province',
        ]);

        $event = Event::query()->create([
            'region_id' => $region->getKey(),
            'title' => 'Agenda Maluku',
            'slug' => 'agenda-maluku',
        ]);

        $region->forceDelete();

        $this->assertDatabaseHas('events', ['id' => $event->getKey()]);
        $this->assertNull($event->fresh()->region_id);
    }

    public function test_aksi_hapus_tunggal_melakukan_soft_delete_tanpa_menghapus_item(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $gallery = $this->galeriDenganItem();

        $this->actingAs($admin);

        Livewire::test(EditGallery::class, ['record' => $gallery->getRouteKey()])
            ->callAction('delete')
            ->assertNotified();

        $this->assertSoftDeleted('galleries', ['id' => $gallery->getKey()]);
        $this->assertDatabaseHas('gallery_items', [
            'gallery_id' => $gallery->getKey(),
            'deleted_at' => null,
        ]);
    }

    public function test_hapus_permanen_tunggal_menampilkan_pesan_ramah_saat_foreign_key_menolak(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $gallery = $this->galeriDenganItem();
        $gallery->delete();

        $this->actingAs($admin);

        Livewire::test(ListGalleries::class)
            ->filterTable('trashed', false)
            ->callTableAction('forceDelete', $gallery)
            ->assertNotified('Data tidak dapat dihapus permanen');

        $this->assertDatabaseHas('galleries', ['id' => $gallery->getKey()]);
        $this->assertDatabaseHas('gallery_items', ['gallery_id' => $gallery->getKey()]);
    }

    public function test_hapus_massal_melakukan_soft_delete_dan_force_delete_massal_menghormati_foreign_key(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $galleryDipakai = $this->galeriDenganItem();
        $galleryKosong = $this->galeri('album-kosong', 2);

        $this->actingAs($admin);

        Livewire::test(ListGalleries::class)
            ->callTableBulkAction('delete', [$galleryDipakai, $galleryKosong])
            ->assertNotified();

        $this->assertSoftDeleted('galleries', ['id' => $galleryDipakai->getKey()]);
        $this->assertSoftDeleted('galleries', ['id' => $galleryKosong->getKey()]);
        $this->assertDatabaseHas('gallery_items', [
            'gallery_id' => $galleryDipakai->getKey(),
            'deleted_at' => null,
        ]);

        Livewire::test(ListGalleries::class)
            ->filterTable('trashed', false)
            ->callTableBulkAction('forceDelete', [$galleryDipakai, $galleryKosong])
            ->assertNotified();

        $this->assertDatabaseHas('galleries', ['id' => $galleryDipakai->getKey()]);
        $this->assertDatabaseHas('gallery_items', ['gallery_id' => $galleryDipakai->getKey()]);
        $this->assertDatabaseMissing('galleries', ['id' => $galleryKosong->getKey()]);
    }

    private function galeriDenganItem(): Gallery
    {
        $gallery = $this->galeri('album-dengan-item', 1);

        GalleryItem::query()->create([
            'gallery_id' => $gallery->getKey(),
            'type' => 'photo',
            'file' => 'uploads/gallery-items/foto-uji.jpg',
            'sort_order' => 1,
        ]);

        return $gallery;
    }

    private function galeri(string $slug, int $sortOrder): Gallery
    {
        return Gallery::query()->create([
            'title' => 'Album '.$slug,
            'slug' => $slug,
            'is_featured' => false,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);
    }
}
