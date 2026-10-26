<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Dua admin dengan kredensial berbeda, menggambarkan kondisi ketika
         * website dikelola lebih dari satu orang.
         */
        config(['perwama.admins' => [
            [
                'name' => 'Administrator Utama',
                'email' => 'admin1@perwama-ina.or.id',
                'password' => 'kata-sandi-utama',
                'phone' => '081200000001',
            ],
            [
                'name' => 'Administrator Kedua',
                'email' => 'admin2@perwama-ina.or.id',
                'password' => 'kata-sandi-kedua',
                'phone' => '081200000002',
            ],
        ]]);

        $this->seed(AdminUserSeeder::class);
    }

    /**
     * Seeder membuat semua akun admin pada konfigurasi, bukan hanya satu akun.
     */
    public function test_seeder_membuat_lebih_dari_satu_admin(): void
    {
        $this->assertSame(2, User::query()->admin()->count());

        $this->assertDatabaseHas('users', ['email' => 'admin1@perwama-ina.or.id', 'is_admin' => true]);
        $this->assertDatabaseHas('users', ['email' => 'admin2@perwama-ina.or.id', 'is_admin' => true]);
    }

    /**
     * Seeder dapat dijalankan berulang tanpa menggandakan akun dan tanpa
     * menimpa kata sandi yang sudah diganti pengelola.
     */
    public function test_seeder_aman_dijalankan_ulang(): void
    {
        $kedua = $this->adminKedua();
        $kedua->update(['password' => 'kata-sandi-baru']);

        $this->seed(AdminUserSeeder::class);

        $this->assertSame(2, User::query()->admin()->count());
        $this->assertTrue(Hash::check('kata-sandi-baru', $kedua->fresh()->password));
    }

    /**
     * Setiap admin dapat membuka panel admin.
     */
    public function test_semua_admin_dapat_membuka_panel(): void
    {
        foreach (User::query()->admin()->get() as $admin) {
            // Sesi dibersihkan agar tiap admin diuji dari kondisi login baru.
            $this->flushSession();

            $this->actingAs($admin)
                ->get('/admin')
                ->assertOk();
        }
    }

    /**
     * Pengguna tanpa akses admin tetap ditolak oleh panel.
     */
    public function test_pengguna_tanpa_akses_admin_ditolak(): void
    {
        $pengguna = User::factory()->create(['is_admin' => false]);

        $this->actingAs($pengguna)
            ->get('/admin')
            ->assertForbidden();
    }

    /**
     * Admin tidak dapat mencabut akses admin miliknya sendiri.
     */
    public function test_admin_tidak_dapat_mencabut_akses_akun_sendiri(): void
    {
        $admin = $this->adminUtama();

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $admin->getKey()])
            ->fillForm(['is_admin' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($admin->fresh()->is_admin);
    }

    /**
     * Admin dapat memberi akses admin kepada pengguna lain.
     */
    public function test_admin_dapat_memberi_akses_admin_kepada_pengguna_lain(): void
    {
        $admin = $this->adminUtama();
        $pengguna = User::factory()->create(['is_admin' => false]);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $pengguna->getKey()])
            ->fillForm(['is_admin' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($pengguna->fresh()->is_admin);
        $this->assertSame(3, User::query()->admin()->count());
    }

    /**
     * Admin terakhir tidak dapat dihapus.
     */
    public function test_admin_terakhir_tidak_dapat_dihapus(): void
    {
        $this->adminKedua()->delete();
        $tersisa = $this->adminUtama();

        $this->assertFalse($tersisa->delete());
        $this->assertDatabaseHas('users', ['email' => 'admin1@perwama-ina.or.id']);
    }

    /**
     * Pengguna tanpa akses admin tetap dapat dihapus seperti biasa
     * (pengaman hanya berlaku untuk admin terakhir).
     */
    public function test_pengguna_biasa_tetap_dapat_dihapus(): void
    {
        $pengguna = User::factory()->create(['is_admin' => false]);

        $this->assertTrue($pengguna->delete());
        $this->assertSoftDeleted('users', ['email' => $pengguna->email]);
    }

    private function adminUtama(): User
    {
        return User::query()->where('email', 'admin1@perwama-ina.or.id')->firstOrFail();
    }

    private function adminKedua(): User
    {
        return User::query()->where('email', 'admin2@perwama-ina.or.id')->firstOrFail();
    }
}
