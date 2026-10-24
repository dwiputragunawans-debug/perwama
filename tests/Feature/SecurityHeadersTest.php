<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Header keamanan dipasang aplikasi, bukan hanya mengandalkan web server
 * (temuan SEC-05 pada audit).
 *
 * CSP sengaja hanya berisi direktif yang tidak memengaruhi skrip/gaya, supaya
 * panel Filament (Alpine/Livewire) dan sematan peta tetap bekerja.
 */
class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_publik_membawa_header_keamanan(): void
    {
        $response = $this->get('/')->assertOk();

        $response
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');

        $this->assertStringContainsString('camera=()', (string) $response->headers->get('Permissions-Policy'));
    }

    public function test_csp_tidak_memblokir_skrip_panel(): void
    {
        $csp = (string) $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);

        // Tanpa default-src/script-src, skrip sebaris Livewire tetap berjalan.
        $this->assertStringNotContainsString('default-src', $csp);
        $this->assertStringNotContainsString('script-src', $csp);
    }

    public function test_halaman_login_panel_membawa_header_keamanan(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_halaman_galat_juga_membawa_header_keamanan(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
