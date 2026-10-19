<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFormLanguageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Formulir "Pendaftar Anggota" (Buat & Ubah) memakai label berbahasa Indonesia.
     */
    public function test_formulir_pendaftar_anggota_memakai_label_indonesia(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $aplikasi = MembershipApplication::create(['full_name' => 'Uji Label']);

        foreach ([
            '/admin/membership-applications/create',
            "/admin/membership-applications/{$aplikasi->getKey()}/edit",
        ] as $url) {
            $response = $this->actingAs($admin)->get($url)->assertOk();

            foreach ([
                'Nama Lengkap', 'NIK', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin',
                'Alamat', 'Kota', 'Provinsi', 'WhatsApp', 'Alamat Email', 'Pendidikan',
                'Pekerjaan', 'Bidang Minat', 'Asal Daerah Maluku', 'Wilayah Domisili',
                'Jenis Keanggotaan', 'Motivasi', 'Status', 'Catatan Admin',
            ] as $label) {
                $response->assertSee($label, false);
            }

            foreach (['Full name', 'Birth place', 'Birth date', 'Email address'] as $english) {
                $response->assertDontSee($english, false);
            }
        }
    }

    /**
     * Formulir "Anggota" (Buat & Ubah) memakai label berbahasa Indonesia.
     */
    public function test_formulir_anggota_memakai_label_indonesia(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $anggota = Member::create(['full_name' => 'Uji Label']);

        foreach ([
            '/admin/members/create',
            "/admin/members/{$anggota->getKey()}/edit",
        ] as $url) {
            $response = $this->actingAs($admin)->get($url)->assertOk();

            foreach ([
                'Nomor Anggota', 'Nama Lengkap', 'Tempat Lahir', 'Tanggal Lahir',
                'Jenis Kelamin', 'Alamat Email', 'Tanggal Bergabung', 'Catatan',
            ] as $label) {
                $response->assertSee($label, false);
            }

            foreach (['Full name', 'Member number', 'Birth place', 'Email address'] as $english) {
                $response->assertDontSee($english, false);
            }
        }
    }

    /**
     * Seluruh field pada semua formulir resource admin sudah diberi label
     * eksplisit, sehingga tidak ada label yang tampil dalam bahasa Inggris.
     */
    public function test_semua_field_formulir_memakai_label_eksplisit(): void
    {
        $tanpaLabel = [];

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $schema = $resource::form(Schema::make());

            // withHidden: true — field bersyarat (visible/required bergantung
            // pada isian lain) tetap diperiksa labelnya tanpa memerlukan
            // konteks Livewire untuk mengevaluasi kondisinya.
            foreach ($schema->getComponents(withHidden: true) as $component) {
                $this->kumpulkanFieldTanpaLabel($component, $resource, $tanpaLabel);
            }
        }

        $this->assertSame(
            [],
            $tanpaLabel,
            "Field formulir berikut belum diberi label: \n- ".implode("\n- ", $tanpaLabel),
        );
    }

    /**
     * @param  array<int, string>  $tanpaLabel
     */
    private function kumpulkanFieldTanpaLabel(SchemaComponent $component, string $resource, array &$tanpaLabel): void
    {
        if ($component instanceof Field && ! $component->hasCustomLabel()) {
            $tanpaLabel[] = class_basename($resource).' ['.$component->getName().']';
        }

        // getDefaultChildComponents() membaca komponen anak tanpa perlu konteks Livewire.
        $children = $component->getDefaultChildComponents();

        if ($children instanceof Schema) {
            $children = $children->getComponents();
        }

        foreach ($children as $child) {
            if ($child instanceof SchemaComponent) {
                $this->kumpulkanFieldTanpaLabel($child, $resource, $tanpaLabel);
            }
        }
    }
}
