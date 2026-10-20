<?php

namespace Tests\Feature;

use App\Filament\Resources\Regions\Pages\ListRegions;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionProperty;
use Tests\TestCase;

class AdminTableLanguageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seluruh kolom pada semua tabel resource admin sudah diberi label
     * eksplisit, sehingga tidak ada judul kolom yang tampil dalam bahasa
     * Inggris hasil turunan otomatis Filament.
     */
    public function test_semua_kolom_tabel_memakai_label_eksplisit(): void
    {
        $tanpaLabel = [];

        // Komponen Livewire apa pun yang mengimplementasikan HasTable cukup
        // untuk membangun tabel tanpa konteks Livewire penuh.
        $livewire = new ListRegions;

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $table = $resource::table(Table::make($livewire));

            foreach ($table->getColumns() as $column) {
                $property = new ReflectionProperty($column, 'label');
                $property->setAccessible(true);

                if ($property->getValue($column) === null) {
                    $tanpaLabel[] = class_basename($resource).' ['.$column->getName().']';
                }
            }
        }

        $this->assertSame(
            [],
            $tanpaLabel,
            "Kolom tabel berikut belum diberi label: \n- ".implode("\n- ", $tanpaLabel),
        );
    }

    /**
     * Kolom "Jenis" wilayah tampil dalam bahasa Indonesia ("Provinsi"),
     * bukan nilai mentah "province" dari basis data.
     */
    public function test_kolom_jenis_wilayah_memakai_label_indonesia(): void
    {
        $this->seed();

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/regions')
            ->assertOk()
            ->assertSee('Provinsi', false)
            ->assertDontSee('>province<', false)
            ->assertDontSee('Province', false);
    }

    /**
     * Daftar "Wilayah" memakai judul kolom berbahasa Indonesia.
     */
    public function test_daftar_wilayah_memakai_label_indonesia(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/regions')->assertOk();

        foreach (['Wilayah Induk', 'Nama', 'Provinsi', 'Pulau', 'Lintang', 'Bujur', 'Gambar Sampul'] as $label) {
            $response->assertSee($label, false);
        }

        foreach (['Cover image', 'Sort order', 'Latitude', 'Longitude'] as $english) {
            $response->assertDontSee($english, false);
        }
    }

    /**
     * Daftar "Anggota" memakai judul kolom berbahasa Indonesia.
     */
    public function test_daftar_anggota_memakai_label_indonesia(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/members')->assertOk();

        foreach (['Nama Lengkap', 'NIK', 'Tempat Lahir', 'Tanggal Lahir', 'Wilayah Domisili', 'Tanggal Bergabung'] as $label) {
            $response->assertSee($label, false);
        }

        foreach (['Full name', 'Member number', 'Birth place', 'Email address', 'Interest field', 'Domicile region'] as $english) {
            $response->assertDontSee($english, false);
        }
    }
}
