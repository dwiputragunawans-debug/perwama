<?php

namespace Tests\Feature;

use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Menu "Cabang" disembunyikan dari sidebar panel tanpa mematikan resource:
 * route dan aksi CRUD tetap tersedia untuk pengelolaan data lama.
 */
class BranchMenuVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_cabang_hilang_dari_sidebar_tetapi_resource_tetap_aktif(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertFalse(BranchResource::shouldRegisterNavigation());

        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

        $this->assertStringNotContainsString('/admin/branches', $html);

        // Route tetap terdaftar sehingga fungsi dan kode resource tidak hilang.
        foreach (['index', 'create', 'edit'] as $page) {
            $this->assertTrue(Route::has('filament.admin.resources.branches.'.$page));
        }

        $this->actingAs($admin)->get('/admin/branches')->assertOk();
        $this->actingAs($admin)->get('/admin/branches/create')->assertOk();

        $branch = Branch::query()->create([
            'name' => 'Ranting Uji Menu',
            'slug' => 'ranting-uji-menu',
            'type' => 'ranting',
            'member_count' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/admin/branches/'.$branch->getKey().'/edit')->assertOk();
    }
}
