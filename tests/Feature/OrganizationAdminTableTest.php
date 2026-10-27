<?php

namespace Tests\Feature;

use App\Filament\Resources\Organizations\OrganizationResource;
use App\Filament\Resources\Organizations\Pages\ListOrganizations;
use App\Models\User;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daftar Organisasi di panel admin hanya menampilkan kolom ringkas dan
 * menerjemahkan jenis organisasi ke label bahasa Indonesia.
 */
class OrganizationAdminTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_kolom_tabel_organisasi_hanya_yang_diperlukan(): void
    {
        $table = OrganizationResource::table(Table::make(new ListOrganizations));

        $kolom = collect($table->getColumns())
            ->map(fn ($column): string => $column->getName())
            ->all();

        foreach (['region.name', 'parent.name', 'name', 'type', 'leader_name', 'secretary_name', 'logo'] as $wajib) {
            $this->assertContains($wajib, $kolom, "Kolom {$wajib} harus tetap tampil.");
        }

        foreach ([
            'slug',
            'phone',
            'email',
            'whatsapp',
            'facebook',
            'instagram',
            'youtube',
            'cover_image',
            'founded_at',
        ] as $dihapus) {
            $this->assertNotContains($dihapus, $kolom, "Kolom {$dihapus} seharusnya tidak tampil.");
        }
    }

    public function test_jenis_organisasi_tampil_dengan_label_indonesia(): void
    {
        $this->seed();

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/organizations')
            ->assertOk()
            ->assertSee('Dewan Pimpinan Daerah (DPD)')
            ->assertSee('Pimpinan Cabang (PC)');
    }
}
