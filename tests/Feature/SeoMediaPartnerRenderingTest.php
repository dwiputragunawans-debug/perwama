<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\News;
use App\Models\OrganizationProfile;
use App\Models\PanelSetting;
use App\Models\Partner;
use App\Models\SeoMetadata;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integrasi konten pendukung ke halaman publik: metadata SEO masuk ke <head>,
 * lampiran Media tampil di halaman detail, dan logo Partner tampil di beranda
 * serta Tentang Kami. Sebelumnya data ini hanya dapat diisi di panel admin.
 */
class SeoMediaPartnerRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PanelSetting::flushCache();
        WebsiteSetting::flushCache();
        OrganizationProfile::flushCache();

        $this->seed();
    }

    public function test_metadata_seo_menimpa_judul_dan_meta_pada_halaman_detail(): void
    {
        $berita = News::query()->published()->ordered()->firstOrFail();

        SeoMetadata::query()->create([
            'model_type' => News::class,
            'model_id' => $berita->getKey(),
            'meta_title' => 'Judul Khusus dari Panel SEO',
            'meta_description' => 'Ringkasan khusus yang diisi pengelola lewat panel.',
            'meta_keywords' => 'perempuan maluku, beasiswa',
            'og_image' => 'https://cdn.example.org/gambar-og.jpg',
            'canonical_url' => 'https://perwama-ina.or.id/berita/kanonik-uji',
        ]);

        $this->get(route('news.show', $berita->slug))
            ->assertOk()
            ->assertSee('<title>Judul Khusus dari Panel SEO</title>', false)
            ->assertSee('<meta name="description" content="Ringkasan khusus yang diisi pengelola lewat panel.">', false)
            ->assertSee('<meta name="keywords" content="perempuan maluku, beasiswa">', false)
            ->assertSee('<link rel="canonical" href="https://perwama-ina.or.id/berita/kanonik-uji">', false)
            ->assertSee('<meta property="og:image" content="https://cdn.example.org/gambar-og.jpg">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
    }

    public function test_halaman_tanpa_metadata_seo_memakai_judul_halaman(): void
    {
        $berita = News::query()->published()->ordered()->firstOrFail();

        $this->get(route('news.show', $berita->slug))
            ->assertOk()
            ->assertSee('<title>'.$berita->title.' — PERWAMA INA</title>', false)
            ->assertSee('<link rel="canonical" href="'.route('news.show', $berita->slug).'">', false);
    }

    public function test_lampiran_media_tampil_di_detail_dan_berkas_kosong_disembunyikan(): void
    {
        $berita = News::query()->published()->ordered()->firstOrFail();

        $berita->media()->create([
            'name' => 'Laporan Tahunan Uji',
            'file_name' => 'laporan-tahunan-uji.pdf',
            'mime_type' => 'application/pdf',
            'path' => 'https://example.org/berkas/laporan-tahunan-uji.pdf',
            'disk' => 'public',
            'size' => 2048,
            'alt' => 'Dokumen uji unduhan',
            'sort_order' => 1,
        ]);

        // Baris tanpa lokasi berkas adalah draf di panel dan tidak boleh tampil.
        $berita->media()->create([
            'name' => 'Lampiran Draf Belum Diunggah',
            'path' => '',
            'sort_order' => 2,
        ]);

        $this->get(route('news.show', $berita->slug))
            ->assertOk()
            ->assertSee('Lampiran &amp; Berkas', false)
            ->assertSee('Laporan Tahunan Uji')
            ->assertSee('https://example.org/berkas/laporan-tahunan-uji.pdf', false)
            ->assertSee('2 KB')
            ->assertSee('Dokumen uji unduhan')
            ->assertDontSee('Lampiran Draf Belum Diunggah');
    }

    public function test_mitra_aktif_tampil_di_beranda_dan_tentang_tanpa_yang_nonaktif(): void
    {
        Partner::query()->create([
            'name' => 'Mitra Nonaktif Uji',
            'type' => 'komunitas',
            'description' => 'Tidak boleh tampil karena statusnya nonaktif.',
            'sort_order' => 99,
            'is_active' => false,
        ]);

        foreach (['/' => route('home'), '/tentang' => route('about')] as $halaman) {
            $this->get($halaman)
                ->assertOk()
                ->assertSee('Universitas Pattimura')
                ->assertSee('Kampus')
                ->assertSee('https://unpatti.ac.id', false)
                ->assertDontSee('Mitra Nonaktif Uji');
        }

        $this->get(route('home'))
            ->assertSee('Bermitra untuk dampak yang lebih luas')
            ->assertSee('Jejaring &amp; Mitra', false);

        $this->get(route('about'))
            ->assertSee('Mitra &amp; Jejaring Kerja Sama', false);
    }

    public function test_konten_belum_terbit_tidak_membocorkan_metadata_maupun_lampiran(): void
    {
        $draf = News::query()->create([
            'title' => 'Berita Draf Rahasia Uji',
            'slug' => 'berita-draf-rahasia-uji',
            'content' => '<p>Belum boleh dibaca publik.</p>',
            'published_at' => null,
        ]);

        SeoMetadata::query()->create([
            'model_type' => News::class,
            'model_id' => $draf->getKey(),
            'meta_title' => 'Metadata Draf Rahasia',
            'meta_description' => 'Tidak boleh muncul di halaman publik.',
        ]);

        Media::query()->create([
            'model_type' => News::class,
            'model_id' => $draf->getKey(),
            'name' => 'Lampiran Draf Rahasia',
            'path' => 'https://example.org/rahasia.pdf',
        ]);

        $this->get(route('news.show', $draf->slug))->assertNotFound();

        $this->get(route('news.index'))
            ->assertOk()
            ->assertDontSee('Berita Draf Rahasia Uji')
            ->assertDontSee('Metadata Draf Rahasia')
            ->assertDontSee('Lampiran Draf Rahasia');
    }
}
