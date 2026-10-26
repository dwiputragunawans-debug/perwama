<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

/**
 * Akun admin tidak boleh dibuat dengan kata sandi bawaan (temuan SEC-01).
 *
 * Seeder harus berhenti pada server sungguhan bila ADMIN_PASSWORD kosong atau
 * mudah ditebak, karena akun seperti itu membuat panel dapat diambil alih.
 */
class AdminUserSeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['perwama.admins' => [[
            'name' => 'Admin Uji',
            'email' => 'admin-uji@example.com',
            'password' => null,
            'phone' => null,
        ]]]);
    }

    public function test_seeder_gagal_di_produksi_tanpa_kata_sandi(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ADMIN_PASSWORD');

        (new AdminUserSeeder)->run();
    }

    public function test_seeder_gagal_di_produksi_dengan_kata_sandi_lemah(): void
    {
        $this->app['env'] = 'production';
        config(['perwama.admins.0.password' => 'password']);

        $this->expectException(RuntimeException::class);

        (new AdminUserSeeder)->run();
    }

    public function test_seeder_gagal_di_produksi_dengan_kata_sandi_terlalu_pendek(): void
    {
        $this->app['env'] = 'production';
        config(['perwama.admins.0.password' => 'pendek123']);

        $this->expectException(RuntimeException::class);

        (new AdminUserSeeder)->run();
    }

    public function test_seeder_membuat_admin_di_produksi_dengan_kata_sandi_kuat(): void
    {
        $this->app['env'] = 'production';
        config(['perwama.admins.0.password' => 'Sandi-Produksi-2026!']);

        (new AdminUserSeeder)->run();

        $admin = User::query()->where('email', 'admin-uji@example.com')->firstOrFail();

        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('Sandi-Produksi-2026!', $admin->password));
    }

    /**
     * Di local/testing seeder tetap berjalan, tetapi memakai kata sandi acak —
     * bukan "password" yang diketahui publik.
     */
    public function test_seeder_lokal_memakai_kata_sandi_acak(): void
    {
        (new AdminUserSeeder)->run();

        $admin = User::query()->where('email', 'admin-uji@example.com')->firstOrFail();

        $this->assertTrue($admin->is_admin);
        $this->assertFalse(Hash::check('password', $admin->password));
        $this->assertGreaterThan(20, strlen($admin->password));
    }

    public function test_seeder_memulihkan_admin_yang_sudah_di_soft_delete(): void
    {
        (new AdminUserSeeder)->run();

        // Admin terakhir memang dilindungi; buat admin lain agar akun uji boleh
        // di-soft-delete sebelum diuji pemulihannya oleh seeder.
        User::factory()->create(['is_admin' => true]);

        $admin = User::query()->where('email', 'admin-uji@example.com')->firstOrFail();
        $admin->delete();

        $this->assertSoftDeleted('users', ['email' => 'admin-uji@example.com']);

        (new AdminUserSeeder)->run();

        $this->assertNotSoftDeleted('users', ['email' => 'admin-uji@example.com']);
        $this->assertSame(1, User::withTrashed()->where('email', 'admin-uji@example.com')->count());
    }

    /**
     * Konfigurasi bawaan tidak lagi menyimpan kata sandi apa pun.
     */
    public function test_config_tidak_memiliki_kata_sandi_bawaan(): void
    {
        $isi = (string) file_get_contents(config_path('perwama.php'));

        $this->assertStringContainsString("env('ADMIN_PASSWORD')", $isi);
        $this->assertStringNotContainsString("env('ADMIN_PASSWORD', 'password')", $isi);
        $this->assertStringNotContainsString("'password' => 'password'", $isi);
    }
}
