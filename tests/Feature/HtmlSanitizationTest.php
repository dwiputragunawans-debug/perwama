<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\OrganizationProfile;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Isi dari editor kaya dirender dengan `{!! !!}`, sehingga HTML-nya harus
 * dibersihkan lebih dulu (temuan SEC-03 pada audit).
 *
 * Uji ini menjaga dua hal sekaligus: penyerang tidak dapat menyisipkan skrip
 * lewat isi yang tersimpan, dan format yang sah (paragraf, tebal, tautan, peta)
 * tetap tampil seperti yang diharapkan admin.
 */
class HtmlSanitizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        OrganizationProfile::flushCache();
    }

    /**
     * Skrip, penangan kejadian, dan URL berbahaya dibuang; teks tetap ada.
     */
    public function test_html_aman_membuang_skrip_dan_atribut_berbahaya(): void
    {
        $hasil = html_aman(
            '<p onclick="alert(1)">Isi <strong>penting</strong>'
            .'<script>alert("xss")</script></p>'
            .'<a href="javascript:alert(2)">tautan</a>'
            .'<img src="x" onerror="alert(3)">'
        );

        $this->assertStringNotContainsString('<script', $hasil);
        $this->assertStringNotContainsString('onclick', $hasil);
        $this->assertStringNotContainsString('onerror', $hasil);
        $this->assertStringNotContainsString('javascript:', $hasil);
        $this->assertStringContainsString('<strong>penting</strong>', $hasil);
        $this->assertStringContainsString('Isi', $hasil);
    }

    /**
     * Trik penulisan skema (`java&#115;cript:`) tetap dikenali setelah HTML
     * dinormalisasi oleh parser.
     */
    public function test_skema_url_yang_disamarkan_tetap_ditolak(): void
    {
        $hasil = html_aman('<a href="java&#115;cript:alert(1)">klik</a>');

        $this->assertStringNotContainsString('javascript', strtolower($hasil));
        $this->assertStringNotContainsString('href', $hasil);
    }

    /**
     * Tautan ke tab baru tidak boleh dapat mengendalikan tab pembuka.
     */
    public function test_tautan_tab_baru_mendapat_rel_noopener(): void
    {
        $hasil = html_aman('<a href="https://perwama-ina.or.id" target="_blank">situs</a>');

        $this->assertStringContainsString('rel="noopener noreferrer"', $hasil);
        $this->assertStringContainsString('href="https://perwama-ina.or.id"', $hasil);
    }

    /**
     * Gaya sebaris dibatasi pada perataan teks; properti yang dapat menutupi
     * halaman atau memuat gambar pihak ketiga dibuang.
     */
    public function test_gaya_sebaris_dibatasi_pada_properti_aman(): void
    {
        $hasil = html_aman('<p style="text-align: center; position: fixed; background: url(//jahat.test/a.png)">teks</p>');

        $this->assertStringContainsString('text-align: center', $hasil);
        $this->assertStringNotContainsString('position', $hasil);
        $this->assertStringNotContainsString('url(', $hasil);

        // Properti yang seluruhnya berbahaya dibuang, teksnya tetap tampil.
        $this->assertSame('<p>x</p>', html_aman('<p style="background:url(javascript:alert(1))">x</p>'));
    }

    /**
     * Halaman berita merender isi tersimpan sebagai HTML, tetapi tanpa skrip.
     */
    public function test_halaman_berita_tidak_merender_skrip_tersimpan(): void
    {
        $news = News::query()->create([
            'title' => 'Berita Uji Sanitasi',
            'slug' => 'berita-uji-sanitasi',
            'content' => '<p>Isi <strong>penting</strong>.<script>alert("xss")</script></p>',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('news.show', $news->slug))
            ->assertOk()
            ->assertSee('<strong>penting</strong>', false)
            ->assertDontSee('alert("xss")', false);
    }

    /**
     * Deskripsi wilayah/lembaga dari RichEditor juga dibersihkan.
     */
    public function test_deskripsi_wilayah_dibersihkan_sebelum_dirender(): void
    {
        $region = Region::query()->create([
            'name' => 'Wilayah Uji',
            'slug' => 'wilayah-uji',
            'type' => 'province',
            'description' => '<p>Deskripsi <em>aman</em>.</p><script>alert(1)</script>',
        ]);

        $description = $region->fresh()->descriptionHtml();

        $this->assertStringContainsString('<em>aman</em>', $description);
        $this->assertStringNotContainsString('<script', $description);
    }

    /**
     * Hanya iframe peta resmi yang boleh tampil di halaman kontak.
     */
    public function test_peta_hanya_menerima_iframe_google_atau_openstreetmap(): void
    {
        $profile = OrganizationProfile::current();

        $profile->update([
            'map_embed' => '<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=1&amp;layer=mapnik"></iframe>'
                .'<iframe src="https://jahat.test/peta"></iframe>'
                .'<script>alert(1)</script>',
        ]);

        OrganizationProfile::flushCache();

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('openstreetmap.org/export/embed.html', false)
            ->assertDontSee('jahat.test', false)
            ->assertDontSee('alert(1)', false);
    }
}
