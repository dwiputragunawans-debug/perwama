<?php

namespace Tests\Feature;

use App\Filament\Forms\Components\RichEditor;
use App\Filament\Resources\OrganizationHistories\OrganizationHistoryResource;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\RichEditor as BaseRichEditor;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Seluruh kolom deskripsi di panel admin memakai RichEditor dengan empat
 * pilihan perataan: rata kiri, rata tengah, rata kanan, dan rata kanan kiri
 * (justify).
 */
class RichEditorToolbarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Toolbar editor deskripsi menampilkan tombol rata kanan kiri.
     */
    public function test_editor_deskripsi_memiliki_tombol_rata_kanan_kiri(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(OrganizationHistoryResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Rata kanan kiri', false);
    }

    /**
     * Tidak ada formulir resource yang masih memakai RichEditor bawaan
     * Filament, sehingga tombol rata kanan kiri selalu ikut tampil.
     */
    public function test_semua_editor_pada_formulir_admin_memakai_editor_kustom(): void
    {
        $editorBawaan = [];

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $schema = $resource::form(Schema::make());

            foreach ($schema->getComponents(withHidden: true) as $component) {
                $this->kumpulkanEditorBawaan($component, $resource, $editorBawaan);
            }
        }

        $this->assertSame(
            [],
            $editorBawaan,
            "Editor berikut masih memakai RichEditor bawaan Filament: \n- ".implode("\n- ", $editorBawaan),
        );
    }

    /**
     * @param  array<int, string>  $editorBawaan
     */
    private function kumpulkanEditorBawaan(SchemaComponent $component, string $resource, array &$editorBawaan): void
    {
        if ($component instanceof BaseRichEditor && ! $component instanceof RichEditor) {
            $editorBawaan[] = class_basename($resource).' ['.$component->getName().']';
        }

        // getDefaultChildComponents() membaca komponen anak tanpa perlu konteks Livewire.
        $children = $component->getDefaultChildComponents();

        if ($children instanceof Schema) {
            $children = $children->getComponents();
        }

        foreach ($children as $child) {
            if ($child instanceof SchemaComponent) {
                $this->kumpulkanEditorBawaan($child, $resource, $editorBawaan);
            }
        }
    }
}
