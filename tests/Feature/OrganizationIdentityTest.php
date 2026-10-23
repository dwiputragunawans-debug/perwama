<?php

namespace Tests\Feature;

use App\Filament\Resources\OrganizationIdentities\OrganizationIdentityResource;
use App\Models\OrganizationIdentity;
use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bagian "Identitas Organisasi" pada halaman Tentang Kami serta pengaturannya
 * di panel admin. Isinya berupa judul dan lirik Mars PERWAMA INA yang disimpan
 * sebagai satu baris data (singleton).
 */
class OrganizationIdentityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Model memakai cache statis; dibersihkan agar tiap uji berdiri sendiri.
        OrganizationIdentity::flushCache();
    }

    /**
     * Lirik bawaan tersedia setelah migrasi sehingga bagian langsung tayang.
     */
    public function test_lirik_bawaan_mars_tersedia(): void
    {
        $identity = OrganizationIdentity::current();

        $this->assertSame('Mars PERWAMA INA', $identity->title);
        $this->assertSame('Ciptaan: Izzak Bakar', $identity->credit());
        $this->assertCount(4, $identity->verses(), 'Lirik bawaan terdiri atas dua bait, refrein, dan bait penutup.');
        $this->assertStringContainsString('Menolong sesama manusia', $identity->verses()[0]);
        $this->assertStringContainsString('Melayani Tuhan dan sesamanya', $identity->verses()[3]);
    }

    /**
     * Deskripsi dipecah menjadi bait pada baris kosong, sedangkan pemenggalan
     * baris di dalam satu bait tetap dipertahankan. Baris "Ciptaan:" dipisahkan
     * sebagai kredit agar tidak ikut ditata sebagai lirik.
     */
    public function test_deskripsi_dipecah_menjadi_bait_dan_kredit(): void
    {
        $identity = OrganizationIdentity::current();

        $identity->update([
            'description' => "Ciptaan: Pencipta Uji\r\n\r\nBait pertama\nBaris kedua\r\n\r\nRef. (2x)\r\n\r\nMajulah bersama",
        ]);

        $this->assertSame('Ciptaan: Pencipta Uji', $identity->credit());
        $this->assertSame(
            ["Bait pertama\nBaris kedua", 'Ref. (2x)', 'Majulah bersama'],
            $identity->verses(),
        );
    }

    /**
     * Bagian tampil setelah CTA, memakai latar dan warna bagian Sejarah, serta
     * membatasi lebar isi 896px dengan teks rata tengah yang membungkus sendiri.
     */
    public function test_bagian_identitas_tampil_setelah_cta_dengan_tata_letak_yang_diinginkan(): void
    {
        $identity = OrganizationIdentity::current();
        $identity->update([
            'title' => 'Mars Uji PERWAMA',
            'description' => "Ciptaan: Pencipta Uji\n\nBait pertama\nBaris kedua\n\nBait kedua",
        ]);
        OrganizationIdentity::flushCache();

        $xpath = $this->xpath('/tentang');
        $section = $xpath->query('//*[@id="identitas-organisasi"]')->item(0);

        $this->assertInstanceOf(DOMElement::class, $section, 'Bagian Identitas Organisasi tidak ditemukan.');

        $kelas = $section->getAttribute('class');

        $this->assertStringContainsString('section-soft', $kelas, 'Latar bagian harus sama dengan bagian Sejarah.');
        $this->assertStringContainsString('section-y', $kelas);

        // Label dan judul memakai gaya yang sama dengan bagian Sejarah.
        $this->assertSame(1, $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " section-eyebrow ")]', $section)->length);

        $judul = $xpath->query('.//h2', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $judul);
        $this->assertSame('Mars Uji PERWAMA', trim($judul->textContent));
        $this->assertStringContainsString('section-title', $judul->getAttribute('class'));

        // Lebar isi 896px (max-w-4xl) dan rata tengah.
        $pembungkus = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " max-w-4xl ")]', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $pembungkus, 'Isi bagian harus dibatasi sekitar 800–900px.');
        $this->assertStringContainsString('text-center', $pembungkus->getAttribute('class'));

        // Kredit pencipta tampil terpisah dengan warna meta.
        $kredit = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " text-meta ")]', $section)->item(0);

        $this->assertInstanceOf(DOMElement::class, $kredit);
        $this->assertSame('Ciptaan: Pencipta Uji', trim($kredit->textContent));

        // Dua bait tampil sebagai paragraf terpisah; baris di dalam bait
        // dipertahankan lewat whitespace-pre-line.
        $bait = $xpath->query('.//p[contains(concat(" ", normalize-space(@class), " "), " whitespace-pre-line ")]', $section);

        $this->assertSame(2, $bait->length);
        $this->assertSame("Bait pertama\nBaris kedua", trim($bait->item(0)->textContent));

        // Bagian ini adalah section terakhir di <main>, tepat sebelum footer,
        // dan didahului CTA "Ingin tahu lebih banyak?".
        $semuaBagian = $xpath->query('//main//section');
        $terakhir = $semuaBagian->item($semuaBagian->length - 1);

        $this->assertSame($section, $terakhir, 'Bagian Identitas Organisasi harus menjadi bagian terakhir sebelum footer.');

        $sebelumnya = $semuaBagian->item($semuaBagian->length - 2);

        $this->assertInstanceOf(DOMElement::class, $sebelumnya);
        $this->assertStringContainsString('Ingin tahu lebih banyak?', $sebelumnya->textContent);
    }

    /**
     * Tanpa deskripsi, bagian tidak dirender agar tidak menyisakan ruang kosong.
     */
    public function test_bagian_identitas_tidak_tampil_bila_deskripsi_kosong(): void
    {
        OrganizationIdentity::current()->update(['description' => null]);
        OrganizationIdentity::flushCache();

        $this->get('/tentang')
            ->assertOk()
            ->assertDontSee('id="identitas-organisasi"', false);
    }

    /**
     * Menu admin "Identitas Organisasi" tersedia pada grup Tentang, hanya
     * memiliki satu record, dan menyediakan isian Judul serta Deskripsi.
     */
    public function test_menu_admin_identitas_organisasi_tersedia_dan_bersifat_tunggal(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertSame('Identitas Organisasi', OrganizationIdentityResource::getNavigationLabel());
        $this->assertSame('Tentang', OrganizationIdentityResource::getNavigationGroup());
        $this->assertFalse(OrganizationIdentityResource::canCreate());

        $this->actingAs($admin)->get('/admin/organization-identities')->assertOk();

        $this->actingAs($admin)
            ->get('/admin/organization-identities/1/edit')
            ->assertOk()
            ->assertSee('Identitas Organisasi', false)
            ->assertSee('title', false)
            ->assertSee('description', false)
            ->assertSee('Mars PERWAMA INA', false);

        OrganizationIdentity::current();
        OrganizationIdentity::current();

        $this->assertSame(1, OrganizationIdentity::query()->count());
    }

    private function xpath(string $url): DOMXPath
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get($url)->assertOk()->getContent());
        libxml_clear_errors();

        return new DOMXPath($dom);
    }
}
