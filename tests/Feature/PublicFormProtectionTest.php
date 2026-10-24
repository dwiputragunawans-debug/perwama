<?php

namespace Tests\Feature;

use App\Support\TrustedProxies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Formulir publik dapat diisi siapa saja tanpa login, sehingga perlu
 * pembatasan laju dan perangkap bot (temuan SEC-02 pada audit).
 */
class PublicFormProtectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kolom perangkap tidak terlihat pengunjung, tetapi tetap ada di formulir.
     */
    public function test_formulir_publik_memiliki_kolom_perangkap(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('name="website"', false);

        $this->get(route('join'))
            ->assertOk()
            ->assertSee('name="website"', false);
    }

    /**
     * Bot yang mengisi kolom perangkap mendapat balasan sukses, tetapi pesannya
     * tidak tersimpan.
     */
    public function test_kolom_perangkap_mencegah_penyimpanan_pesan_kontak(): void
    {
        $this->post('/kontak', [
            'name' => 'Bot Pengirim',
            'email' => 'bot@example.com',
            'message' => 'Pesan spam yang tidak boleh tersimpan.',
            'website' => 'https://spam.example',
        ])
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('contact_messages', 0);
    }

    /**
     * Pendaftaran anggota dan permohonan mitra juga menolak bot secara senyap.
     */
    public function test_kolom_perangkap_mencegah_penyimpanan_pendaftaran(): void
    {
        $this->post('/bergabung', [
            'full_name' => 'Bot Pendaftar',
            'whatsapp' => '081200000099',
            'membership_type' => 'anggota',
            'website' => 'https://spam.example',
        ])->assertRedirect(route('join'));

        $this->post('/bergabung/mitra', [
            'name' => 'Bot Mitra',
            'email' => 'mitra-bot@example.com',
            'type' => 'mitra',
            'message' => 'Permohonan kerja sama dari bot.',
            'website' => 'https://spam.example',
        ])->assertRedirect(route('join'));

        $this->assertDatabaseCount('membership_applications', 0);
        $this->assertDatabaseCount('partnership_requests', 0);
    }

    /**
     * Endpoint mitra harus punya formulir nyata di halaman Bergabung — bukan
     * rute tanpa tampilan (temuan tautan mati).
     */
    public function test_formulir_permohonan_mitra_tersedia_dan_dapat_dikirim(): void
    {
        $this->get(route('join'))
            ->assertOk()
            ->assertSee('Ajukan kemitraan atau kolaborasi')
            ->assertSee('action="'.route('join.partnership').'"', false);

        $this->post(route('join.partnership'), [
            'name' => 'Penanggung Jawab Mitra',
            'organization_name' => 'Yayasan Mitra Maluku',
            'email' => 'mitra@example.com',
            'phone' => '081200000077',
            'type' => 'mitra',
            'message' => 'Kami ingin berkolaborasi dalam program pendidikan.',
        ])
            ->assertRedirect(route('join'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('partnership_requests', [
            'name' => 'Penanggung Jawab Mitra',
            'organization_name' => 'Yayasan Mitra Maluku',
            'email' => 'mitra@example.com',
            'type' => 'mitra',
            'status' => 'pending',
        ]);
    }

    /**
     * Pengiriman formulir kontak dibatasi lima kali per sepuluh menit.
     */
    public function test_formulir_kontak_dibatasi_setelah_lima_pengiriman(): void
    {
        $pesan = [
            'name' => 'Pengunjung Uji',
            'email' => 'pengunjung@example.com',
            'message' => 'Pesan uji pembatasan laju.',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/kontak', $pesan)->assertRedirect(route('contact'));
        }

        $this->post('/kontak', $pesan)->assertStatus(429);
        $this->assertDatabaseCount('contact_messages', 5);
    }

    /**
     * Pendaftaran anggota dan mitra berbagi batas lima permohonan per jam.
     */
    public function test_formulir_pendaftaran_dibatasi_per_jam(): void
    {
        $pendaftaran = [
            'full_name' => 'Calon Anggota',
            'whatsapp' => '081200000098',
            'membership_type' => 'anggota',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/bergabung', $pendaftaran)->assertRedirect(route('join'));
        }

        $this->post('/bergabung', $pendaftaran)->assertStatus(429);

        // Endpoint mitra memakai batas yang sama untuk alamat IP ini.
        $this->post('/bergabung/mitra', [
            'name' => 'Calon Mitra',
            'email' => 'mitra@example.com',
            'type' => 'mitra',
            'message' => 'Permohonan kerja sama.',
        ])->assertStatus(429);
    }

    /**
     * Endpoint telemetri dan penyegar sesi juga berada di belakang throttle.
     */
    public function test_endpoint_telemetri_dan_sesi_memakai_pembatasan_laju(): void
    {
        $routes = Route::getRoutes();

        $this->assertContains('throttle:telemetri', $routes->getByName('visit.duration')->gatherMiddleware());
        $this->assertContains('throttle:sesi-segar', $routes->getByName('session.refresh')->gatherMiddleware());
    }

    /**
     * Proxy yang dikonfigurasi membuat alamat IP tersimpan mengikuti
     * X-Forwarded-For — penting agar batas laju dihitung per pengunjung.
     */
    public function test_proxy_terpercaya_memengaruhi_alamat_ip_tersimpan(): void
    {
        config(['perwama.trusted_proxies' => '127.0.0.1']);

        $this->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.9',
        ])->post('/kontak', [
            'name' => 'Pengunjung Proxy',
            'email' => 'proxy-diizinkan@example.com',
            'message' => 'Alamat IP diambil dari proxy terpercaya.',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'proxy-diizinkan@example.com',
            'ip_address' => '203.0.113.9',
        ]);
    }

    public function test_header_proxy_diabaikan_bila_tidak_dikonfigurasi(): void
    {
        config(['perwama.trusted_proxies' => null]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.7',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.99',
        ])->post('/kontak', [
            'name' => 'Pengunjung Langsung',
            'email' => 'proxy-ditolak@example.com',
            'message' => 'Header proxy harus diabaikan.',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'proxy-ditolak@example.com',
            'ip_address' => '198.51.100.7',
        ]);
    }

    /**
     * Proxy hanya dipercaya bila dikonfigurasi, agar batas laju tidak dapat
     * dilewati dengan memalsukan header X-Forwarded-For.
     */
    public function test_alamat_ip_memakai_header_proxy_hanya_bila_dipercaya(): void
    {
        $server = [
            'REMOTE_ADDR' => '10.0.0.5',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.9',
        ];

        TrustedProxies::apply(null);
        $this->assertSame('10.0.0.5', Request::create('/kontak', 'POST', [], [], [], $server)->ip());

        TrustedProxies::apply('10.0.0.5');
        $this->assertSame('203.0.113.9', Request::create('/kontak', 'POST', [], [], [], $server)->ip());

        TrustedProxies::apply('10.0.0.1, 10.0.0.2');
        $this->assertSame(['10.0.0.1', '10.0.0.2'], Request::getTrustedProxies());

        // Dikembalikan agar uji lain tidak mewarisi state statis.
        TrustedProxies::apply(null);
        $this->assertSame([], Request::getTrustedProxies());
    }
}
