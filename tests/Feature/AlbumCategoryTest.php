<?php

namespace Tests\Feature;

use App\Filament\Resources\AlbumCategories\AlbumCategoryResource;
use App\Filament\Resources\AlbumCategories\Pages\CreateAlbumCategory;
use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Models\AlbumCategory;
use App\Models\Gallery;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Kategori Album dikelola sebagai master data di grup menu Galeri dan dipakai
 * oleh form tambah/ubah Galeri serta filter album pada halaman publik.
 */
class AlbumCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->seed();
    }

    /**
     * Kategori bawaan tersedia pada menu Galeri dan dapat dibuka admin.
     */
    public function test_master_kategori_album_tersedia_di_menu_galeri(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertSame('Galeri', AlbumCategoryResource::getNavigationGroup());
        $this->assertSame('Kategori Album', AlbumCategoryResource::getNavigationLabel());
        $this->assertSame('Kategori Album', AlbumCategoryResource::getModelLabel());

        $this->actingAs($admin)
            ->get('/admin/album-categories')
            ->assertOk()
            ->assertSee('Kategori Album')
            ->assertSee('HUT PERWAMA INA');

        foreach ([
            'hut_perwama_ina', 'pelatihan', 'bakti_sosial', 'seminar',
            'rapat_organisasi', 'kunjungan', 'kegiatan_daerah', 'budaya',
        ] as $slug) {
            $this->assertDatabaseHas('album_categories', ['slug' => $slug]);
        }
    }

    /**
     * Kategori yang baru dibuat langsung menjadi pilihan pada form Galeri,
     * tanpa perlu mengubah kode.
     */
    public function test_kategori_baru_langsung_tersedia_pada_form_galeri(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        Livewire::test(CreateAlbumCategory::class)
            ->fillForm([
                'name' => 'Dokumentasi Khusus',
                'sort_order' => 99,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = AlbumCategory::query()->where('name', 'Dokumentasi Khusus')->firstOrFail();

        $this->assertSame('dokumentasi-khusus', $category->slug);

        Livewire::test(CreateGallery::class)
            ->assertSchemaComponentExists(
                'album_category_id',
                checkComponentUsing: fn (Select $select): bool => array_key_exists(
                    $category->getKey(),
                    $select->getOptions(),
                ),
            );
    }

    /**
     * Kategori yang dipilih tersimpan saat Galeri dibuat maupun diubah, lalu
     * tampil pada halaman detail serta filter album publik.
     */
    public function test_galeri_menyimpan_kategori_dari_master_data(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $pelatihan = AlbumCategory::query()->where('slug', 'pelatihan')->firstOrFail();
        $seminar = AlbumCategory::query()->where('slug', 'seminar')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(CreateGallery::class)
            ->fillForm([
                'album_category_id' => $pelatihan->getKey(),
                'title' => 'Album Uji Kategori',
                'slug' => 'album-uji-kategori',
                'is_featured' => false,
                'sort_order' => 100,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $gallery = Gallery::query()->where('slug', 'album-uji-kategori')->firstOrFail();

        $this->assertSame($pelatihan->getKey(), $gallery->album_category_id);
        $this->assertSame('Pelatihan', $gallery->albumLabel());

        Livewire::test(EditGallery::class, ['record' => $gallery->getRouteKey()])
            ->fillForm(['album_category_id' => $seminar->getKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($seminar->getKey(), $gallery->refresh()->album_category_id);

        $this->get(route('gallery.index', ['album' => 'seminar']))
            ->assertOk()
            ->assertSee('Album Uji Kategori')
            ->assertSee('Seminar');

        $this->get(route('gallery.show', $gallery->slug))
            ->assertOk()
            ->assertSee('Seminar');
    }
}
