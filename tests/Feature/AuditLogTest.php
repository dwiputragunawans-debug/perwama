<?php

namespace Tests\Feature;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AuditLog;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Log Aktivitas di panel sebelumnya tidak pernah terisi karena tidak ada kode
 * yang menulis ke tabel `audit_logs`. Sekarang setiap perubahan oleh pengguna
 * yang login dicatat, dan resource-nya hanya baca.
 */
class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_perubahan_oleh_admin_tercatat_di_log(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        $news = News::query()->create([
            'title' => 'Judul Lama',
            'slug' => 'berita-log-audit',
            'content' => '<p>Isi berita.</p>',
            'published_at' => now()->subDay(),
        ]);

        $dibuat = AuditLog::query()->where('action', 'created')->latest('id')->firstOrFail();

        $this->assertSame($admin->getKey(), $dibuat->user_id);
        $this->assertSame(News::class, $dibuat->model_type);
        $this->assertSame($news->getKey(), $dibuat->model_id);
        $this->assertSame('Judul Lama', $dibuat->new_values['title']);

        $news->update(['title' => 'Judul Baru']);

        $diubah = AuditLog::query()->where('action', 'updated')->latest('id')->firstOrFail();

        $this->assertSame('Judul Lama', $diubah->old_values['title']);
        $this->assertSame('Judul Baru', $diubah->new_values['title']);
    }

    public function test_perubahan_tanpa_login_tidak_dicatat(): void
    {
        News::query()->create([
            'title' => 'Berita Pengunjung',
            'slug' => 'berita-pengunjung',
            'published_at' => now()->subDay(),
        ]);

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_kata_sandi_tidak_pernah_masuk_log(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $admin->update(['password' => Hash::make('sandi-baru-2026')]);

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_resource_log_bersifat_hanya_baca(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertFalse(AuditLogResource::canCreate());
        $this->assertSame(['index'], array_keys(AuditLogResource::getPages()));

        $this->actingAs($admin)->get('/admin/audit-logs')->assertOk();
        $this->actingAs($admin)->get('/admin/audit-logs/create')->assertNotFound();
    }
}
