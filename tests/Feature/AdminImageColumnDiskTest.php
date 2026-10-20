<?php

namespace Tests\Feature;

use App\Filament\Resources\Regions\Pages\ListRegions;
use App\Models\Management;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminImageColumnDiskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Semua `FileUpload` resource mengunggah berkas ke disk `public`
     * (`storage/app/public/...`), sedangkan `FILESYSTEM_DISK=local` membuat
     * disk bawaan Filament ikut menjadi `local` (`storage/app/private`).
     * `ImageColumn` yang tidak menyebut `->disk('public')` membaca disk privat
     * tersebut; karena disk `local` tidak punya URL, alamat gambar yang
     * dibentuk tidak menunjuk ke `/storage/...` sehingga thumbnail tampil
     * sebagai ikon gambar rusak (mis. kolom "Foto" di daftar Pengurus).
     */
    public function test_kolom_gambar_admin_membaca_disk_public(): void
    {
        $salah = [];

        // Komponen Livewire apa pun yang mengimplementasikan HasTable cukup
        // untuk membangun tabel tanpa konteks Livewire penuh.
        $livewire = new ListRegions;

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $table = $resource::table(Table::make($livewire));

            foreach ($table->getColumns() as $kolom) {
                if (! $kolom instanceof ImageColumn) {
                    continue;
                }

                if ($kolom->getDiskName() !== 'public') {
                    $salah[] = class_basename($resource).' ['.$kolom->getName().'] => '.$kolom->getDiskName();
                }
            }
        }

        $this->assertSame(
            [],
            $salah,
            "Kolom gambar berikut tidak membaca disk `public` sehingga gambarnya rusak:\n- ".implode("\n- ", $salah),
        );
    }

    /**
     * Foto pengurus yang tersimpan di `storage/app/public/uploads/managements`
     * benar-benar dirender lewat tautan `/storage/...` pada daftar admin,
     * bukan sebagai berkas rusak.
     */
    public function test_foto_pengurus_dirender_lewat_tautan_storage_publik(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/managements/foto-pengurus.jpg', 'berkas-uji');

        $admin = User::factory()->create(['is_admin' => true]);

        Management::create([
            'name' => 'Rosalina Melatunan, S.E.',
            'position' => 'Ketua Umum',
            'level' => 'dpp',
            'period' => '2024 — 2029',
            'photo' => 'uploads/managements/foto-pengurus.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/management')->assertOk();

        $response->assertSee('storage/uploads/managements/foto-pengurus.jpg', false);
        $response->assertDontSee('"uploads/managements/foto-pengurus.jpg"', false);
    }
}
