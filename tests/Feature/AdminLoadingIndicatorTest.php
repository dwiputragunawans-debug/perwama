<?php

namespace Tests\Feature;

use App\Models\PanelSetting;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Animasi loading panel admin (resources/views/filament/loading-indicator).
 *
 * Panel memakai navigasi halaman penuh dan formulir Livewire, sehingga tanpa
 * indikator pengguna tidak tahu proses sedang berjalan dan cenderung menekan
 * tombol dua kali. Uji ini memastikan indikator tersuntik pada setiap halaman
 * panel beserta penanganan formulir, tautan, dan permintaan Livewire.
 */
class AdminLoadingIndicatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pengaturan panel & situs memakai cache statis; dibersihkan agar tiap uji berdiri sendiri.
        PanelSetting::flushCache();
        WebsiteSetting::flushCache();
    }

    /**
     * Halaman login juga memakai formulir Livewire, sehingga indikator harus
     * tersedia sebelum pengguna masuk ke panel.
     */
    public function test_halaman_login_menampilkan_indikator_proses(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('id="panel-loading"', false)
            ->assertSee('data-panel-loading-label', false)
            ->assertSee('Memproses…', false)
            ->assertSee('Mohon tunggu, jangan menutup halaman ini.', false)
            // Status dibacakan pembaca layar tanpa mengganggu fokus pengguna.
            ->assertSee('role="status"', false)
            ->assertSee('aria-live="polite"', false);
    }

    /**
     * Indikator ikut tampil di halaman dalam panel (mis. dasbor), bukan hanya
     * di halaman login.
     */
    public function test_indikator_tampil_pada_halaman_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $halaman = $this->actingAs($admin)->get('/admin')->assertOk();

        $halaman
            ->assertSee('id="panel-loading"', false)
            ->assertSee('Memuat halaman…', false);

        // Hanya satu indikator per halaman walaupun panel merender banyak hook.
        $this->assertSame(1, substr_count($halaman->getContent(), 'id="panel-loading"'));
    }

    /**
     * Indikator mengunci klik ganda pada formulir dan menampilkan overlay,
     * sementara perpindahan halaman dipantau lewat klik tautan internal.
     */
    public function test_indikator_memblokir_klik_ganda_dan_memantau_perpindahan_halaman(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();

        // Lapisan pemblokir aktif selama proses berjalan (mencegah klik kedua).
        $this->assertStringContainsString(".panel-loading[data-busy='true']", $html);
        $this->assertStringContainsString('pointer-events: auto', $html);

        // Formulir: kiriman kedua dalam jendela waktu singkat dibatalkan.
        $this->assertStringContainsString("document.addEventListener(\n            'submit',", $html);
        $this->assertStringContainsString('event.stopImmediatePropagation()', $html);

        // Formulir simpan memakai label "Menyimpan…"; login dan modal aksi
        // (mis. konfirmasi hapus) memakai label umum karena tidak menyimpan.
        $this->assertStringContainsString("const isActionModal = submitMethod.includes('callMountedAction')", $html);
        $this->assertStringContainsString(
            "beginOverlay(isLogin || isActionModal ? 'Memproses…' : 'Menyimpan…')",
            $html,
        );

        // Perpindahan halaman: hanya tautan internal pada tab yang sama.
        $this->assertStringContainsString("event.target.closest('a[href]')", $html);
        $this->assertStringContainsString("beginOverlay('Memuat halaman…')", $html);
    }

    /**
     * Proses di antarmuka (simpan, hapus, aksi tabel) dipantau melalui hook
     * Livewire, sedangkan permintaan latar memakai bilah progres.
     */
    public function test_indikator_memantau_permintaan_livewire(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();

        $this->assertStringContainsString("document.addEventListener('livewire:init', registerLivewireHooks)", $html);
        $this->assertStringContainsString("window.Livewire.hook('request'", $html);

        // Pencarian/filter (`$commit`) dan polling (`$refresh`) tidak memicu overlay penuh.
        $this->assertStringContainsString("!method.startsWith('\$')", $html);
        $this->assertStringContainsString('data-variant="progress"', $html);
    }
}
