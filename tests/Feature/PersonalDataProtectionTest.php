<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Temuan SEC-04: NIK dan data pribadi pendaftar tidak boleh tersimpan sebagai
 * teks biasa, tidak boleh tampil utuh di panel, tidak boleh tersalin ke log
 * aktivitas, dan tidak boleh disimpan selamanya setelah pendaftaran ditolak.
 */
class PersonalDataProtectionTest extends TestCase
{
    use RefreshDatabase;

    private const NIK = '3201010101011234';

    public function test_nik_pendaftaran_dienkripsi_saat_disimpan_dan_dibaca_kembali(): void
    {
        $pendaftaran = MembershipApplication::query()->create([
            'full_name' => 'Calon Anggota',
            'nik' => self::NIK,
            'whatsapp' => '081200000001',
            'membership_type' => 'anggota',
            'status' => 'pending',
        ]);

        $tersimpan = DB::table('membership_applications')->where('id', $pendaftaran->id)->value('nik');

        $this->assertNotSame(self::NIK, $tersimpan);
        $this->assertStringNotContainsString(self::NIK, (string) $tersimpan);
        $this->assertSame(self::NIK, $pendaftaran->fresh()->nik);
    }

    public function test_nik_anggota_juga_dienkripsi(): void
    {
        $anggota = Member::query()->create([
            'full_name' => 'Anggota Aktif',
            'nik' => self::NIK,
            'status' => 'active',
        ]);

        $tersimpan = DB::table('members')->where('id', $anggota->id)->value('nik');

        $this->assertNotSame(self::NIK, $tersimpan);
        $this->assertSame(self::NIK, $anggota->fresh()->nik);
    }

    public function test_nik_lama_yang_masih_teks_biasa_tetap_dapat_dibaca(): void
    {
        DB::table('membership_applications')->insert([
            'full_name' => 'Data Lama',
            'nik' => self::NIK,
            'membership_type' => 'anggota',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pendaftaran = MembershipApplication::query()->firstOrFail();

        $this->assertSame(self::NIK, $pendaftaran->nik);
    }

    public function test_nik_ditampilkan_bertopeng_di_panel(): void
    {
        $pendaftaran = MembershipApplication::query()->create([
            'full_name' => 'Calon Anggota',
            'nik' => self::NIK,
            'whatsapp' => '081200000001',
            'membership_type' => 'anggota',
            'status' => 'pending',
        ]);

        $this->assertSame('••••••••••••1234', $pendaftaran->maskedNik());
        $this->assertStringNotContainsString(self::NIK, (string) $pendaftaran->maskedNik());

        $pendaftaran->nik = null;
        $this->assertNull($pendaftaran->maskedNik());
    }

    public function test_nik_tidak_tersalin_ke_log_aktivitas(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $pendaftaran = MembershipApplication::query()->create([
            'full_name' => 'Calon Anggota',
            'nik' => self::NIK,
            'address' => 'Jalan Rahasia Nomor 1',
            'birth_place' => 'Ambon',
            'birth_date' => '1995-01-01',
            'whatsapp' => '081200000001',
            'membership_type' => 'anggota',
            'status' => 'pending',
        ]);

        $log = AuditLog::query()->latest('id')->firstOrFail();
        $isiLog = json_encode([$log->old_values, $log->new_values]);

        $this->assertArrayNotHasKey('nik', $log->new_values);
        $this->assertArrayNotHasKey('address', $log->new_values);
        $this->assertArrayNotHasKey('birth_date', $log->new_values);
        $this->assertArrayNotHasKey('birth_place', $log->new_values);
        $this->assertStringNotContainsString(self::NIK, (string) $isiLog);

        $pendaftaran->update(['address' => 'Alamat Baru']);

        $log = AuditLog::query()->latest('id')->firstOrFail();
        $this->assertArrayNotHasKey('nik', $log->old_values);
        $this->assertArrayNotHasKey('nik', $log->new_values);
    }

    public function test_perintah_retensi_menganonimkan_pendaftaran_ditolak_yang_lama(): void
    {
        config(['perwama.membership_retention_days' => 30]);

        $lama = MembershipApplication::query()->create([
            'full_name' => 'Pendaftar Lama',
            'nik' => self::NIK,
            'birth_place' => 'Ambon',
            'birth_date' => '1995-01-01',
            'address' => 'Jalan Rahasia Nomor 1',
            'city' => 'Ambon',
            'province' => 'Maluku',
            'whatsapp' => '081200000001',
            'email' => 'lama@example.com',
            'membership_type' => 'anggota',
            'motivation' => 'Ingin bergabung.',
            'admin_notes' => 'Catatan berisi data pribadi.',
            'status' => 'rejected',
            'reviewed_at' => now()->subDays(60),
        ]);

        $baru = MembershipApplication::query()->create([
            'full_name' => 'Pendaftar Baru',
            'nik' => self::NIK,
            'whatsapp' => '081200000002',
            'membership_type' => 'anggota',
            'status' => 'rejected',
            'reviewed_at' => now()->subDays(5),
        ]);

        $menunggu = MembershipApplication::query()->create([
            'full_name' => 'Pendaftar Menunggu',
            'nik' => self::NIK,
            'whatsapp' => '081200000003',
            'membership_type' => 'anggota',
            'status' => 'pending',
        ]);

        $this->artisan('membership:purge-rejected')
            ->expectsOutputToContain('1 pendaftaran ditolak dianonimkan')
            ->assertSuccessful();

        $lama->refresh();
        $this->assertSame('Pendaftar Ditolak (dianonimkan)', $lama->full_name);
        $this->assertNull($lama->nik);
        $this->assertNull($lama->birth_place);
        $this->assertNull($lama->birth_date);
        $this->assertNull($lama->address);
        $this->assertNull($lama->city);
        $this->assertNull($lama->province);
        $this->assertNull($lama->whatsapp);
        $this->assertNull($lama->email);
        $this->assertNull($lama->motivation);
        $this->assertNull($lama->admin_notes);
        $this->assertSame('rejected', $lama->status);

        // Yang belum melewati masa retensi dan yang masih menunggu tidak boleh
        // tersentuh.
        $this->assertSame('Pendaftar Baru', $baru->fresh()->full_name);
        $this->assertSame(self::NIK, $baru->fresh()->nik);
        $this->assertSame('Pendaftar Menunggu', $menunggu->fresh()->full_name);
        $this->assertSame(self::NIK, $menunggu->fresh()->nik);
    }

    public function test_perintah_retensi_menolak_masa_retensi_tidak_valid(): void
    {
        $this->artisan('membership:purge-rejected', ['--days' => 0])
            ->expectsOutput('Masa retensi harus minimal 1 hari.')
            ->assertFailed();
    }
}
