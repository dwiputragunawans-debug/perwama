<?php

namespace Tests\Feature;

use App\Filament\Resources\News\Pages\ListNews;
use App\Models\Activity;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\News;
use App\Models\Organization;
use App\Models\OrganizationFigure;
use App\Models\OrganizationHistory;
use App\Models\OrganizationIdentity;
use App\Models\OrganizationProfile;
use App\Models\Partner;
use App\Models\Program;
use App\Models\Publication;
use App\Models\Region;
use App\Models\Story;
use App\Models\User;
use App\Models\Video;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Soft delete menjaga data tetap tersimpan, menyembunyikannya dari situs
 * publik, dan menyediakan jalur pemulihan lewat panel admin.
 */
class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Model yang direkomendasikan README harus memiliki kolom `deleted_at`
     * sekaligus memasang trait SoftDeletes.
     *
     * @return array<class-string, string>
     */
    private function modelTabel(): array
    {
        return [
            User::class => 'users',
            Member::class => 'members',
            MembershipApplication::class => 'membership_applications',
            Gallery::class => 'galleries',
            GalleryItem::class => 'gallery_items',
            News::class => 'news',
            Story::class => 'stories',
            Activity::class => 'activities',
            Event::class => 'events',
            Publication::class => 'publications',
            Video::class => 'videos',
            OrganizationProfile::class => 'organization_profiles',
            OrganizationHistory::class => 'organization_histories',
            OrganizationIdentity::class => 'organization_identities',
            OrganizationFigure::class => 'organization_figures',
            Partner::class => 'partners',
            Program::class => 'programs',
            Region::class => 'regions',
            Organization::class => 'organizations',
        ];
    }

    public function test_semua_tabel_yang_direkomendasikan_memakai_soft_deletes(): void
    {
        foreach ($this->modelTabel() as $model => $table) {
            $this->assertTrue(
                Schema::hasColumn($table, 'deleted_at'),
                "Tabel [{$table}] belum memiliki kolom deleted_at.",
            );

            $this->assertContains(
                SoftDeletes::class,
                class_uses_recursive($model),
                "Model [{$model}] belum memasang trait SoftDeletes.",
            );
        }
    }

    public function test_konten_terhapus_tidak_tampil_di_situs_publik_tetapi_masih_tersimpan(): void
    {
        $this->seed();

        $berita = News::query()->published()->ordered()->firstOrFail();
        $url = route('news.show', $berita->slug);

        $this->get($url)->assertOk();

        $berita->delete();

        $this->assertSoftDeleted('news', ['id' => $berita->getKey()]);
        $this->assertNull(News::query()->find($berita->getKey()));
        $this->assertNotNull(News::withTrashed()->find($berita->getKey()));
        $this->get($url)->assertNotFound();
    }

    public function test_admin_dapat_memulihkan_konten_yang_di_soft_delete(): void
    {
        $this->seed();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = User::factory()->create(['is_admin' => true]);
        $berita = News::query()->published()->ordered()->firstOrFail();
        $berita->delete();

        $this->actingAs($admin);

        Livewire::test(ListNews::class)
            ->filterTable('trashed', false)
            ->callTableAction('restore', $berita)
            ->assertNotified();

        $this->assertNotSoftDeleted('news', ['id' => $berita->getKey()]);
        $this->assertNotNull(News::query()->find($berita->getKey()));
    }

    public function test_slug_otomatis_tidak_menabrak_slug_milik_data_terhapus(): void
    {
        $lama = News::query()->create([
            'title' => 'Judul Kembar',
            'slug' => 'judul-kembar',
        ]);

        $lama->delete();

        $baru = News::query()->create([
            'title' => 'Judul Kembar',
        ]);

        $this->assertSame('judul-kembar-2', $baru->slug);
    }
}
