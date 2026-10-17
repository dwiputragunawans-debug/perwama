<?php

namespace Tests\Feature;

use App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem;
use App\Filament\Resources\Statistics\Pages\CreateStatistic;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Management;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Organization;
use App\Models\OrganizationHistory;
use App\Models\OrganizationProfile;
use App\Models\PanelSetting;
use App\Models\Partner;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Region;
use App\Models\Statistic;
use App\Models\User;
use App\Models\Video;
use App\Models\WebsiteSetting;
use DOMDocument;
use DOMXPath;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Kolom Urutan pada panel admin harus menentukan susunan tampil di situs
 * publik, dan daftar admin harus memperlihatkan susunan yang sama.
 *
 * Setiap uji membandingkan halaman yang benar-benar dirender dengan urutan
 * `sort_order` di basis data — bukan sekadar memanggil scope `ordered()` —
 * sehingga regresi pada view/controller ikut tertangkap.
 */
class SortOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        // Pengaturan & menu memakai cache statis; dibersihkan agar tiap uji mandiri.
        PanelSetting::flushCache();
        WebsiteSetting::flushCache();
        OrganizationProfile::flushCache();
        MenuItem::flushNavigationCache();

        $this->seed();
    }

    /**
     * Seluruh tabel admin yang memiliki kolom Urutan menampilkan baris sesuai
     * kolom itu (angka kecil lebih dulu), bukan sesuai urutan input (id).
     */
    public function test_tabel_admin_mengurutkan_baris_menurut_kolom_urutan(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $daftar = [
            '/admin/galleries' => Gallery::class,
            '/admin/gallery-items' => GalleryItem::class,
            '/admin/management' => Management::class,
            '/admin/media' => Media::class,
            '/admin/menu-items' => MenuItem::class,
            '/admin/organization-histories' => OrganizationHistory::class,
            '/admin/organizations' => Organization::class,
            '/admin/partners' => Partner::class,
            '/admin/program-categories' => ProgramCategory::class,
            '/admin/programs' => Program::class,
            '/admin/regions' => Region::class,
            '/admin/statistics' => Statistic::class,
            '/admin/videos' => Video::class,
        ];

        foreach ($daftar as $url => $model) {
            $records = $model::query()->orderBy('id')->get();

            // Tabel tanpa data (mis. Media) hanya perlu terbuka tanpa galat.
            if ($records->isEmpty()) {
                $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();

                preg_match_all('/table\.records\.(\d+)/', $html, $cocok);

                $this->assertSame(
                    [],
                    array_values(array_unique(array_map('intval', $cocok[1]))),
                    "Tabel [{$url}] menampilkan baris padahal datanya kosong.",
                );

                continue;
            }

            $total = $records->count();

            // Balik nomor urut agar susunannya berbeda dari urutan id (bawaan
            // tabel) — tanpa ini uji bisa lolos hanya karena data seeder
            // kebetulan sudah berurutan.
            foreach ($records as $index => $record) {
                $record->update(['sort_order' => $total - $index]);
            }

            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();

            preg_match_all('/table\.records\.(\d+)/', $html, $cocok);

            $ids = array_values(array_unique(array_map('intval', $cocok[1])));

            $this->assertNotEmpty($ids, "Tabel [{$url}] tidak menampilkan satu pun baris.");

            $diharapkan = $this->urutkanMenurutUrutan($model)
                ->take(count($ids))
                ->pluck('id')
                ->all();

            $this->assertSame(
                $diharapkan,
                $ids,
                "Tabel [{$url}] tidak mengikuti kolom Urutan pada panel admin.",
            );
        }
    }

    /**
     * Form "Buat" seluruh resource berkolom Urutan tetap dapat dibuka setelah
     * field Urutan diseragamkan (validasi, nilai bawaan, dan helper text).
     */
    public function test_halaman_buat_resource_berkolom_urutan_dapat_dibuka(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $halaman = [
            '/admin/galleries/create',
            '/admin/gallery-items/create',
            '/admin/management/create',
            '/admin/media/create',
            '/admin/menu-items/create',
            '/admin/organization-histories/create',
            '/admin/organizations/create',
            '/admin/partners/create',
            '/admin/program-categories/create',
            '/admin/programs/create',
            '/admin/regions/create',
            '/admin/statistics/create',
            '/admin/videos/create',
        ];

        foreach ($halaman as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    /**
     * Record baru otomatis mendapat nomor urut setelah nilai terbesar,
     * sehingga tidak menyelip ke paling atas daftar publik.
     */
    public function test_form_baru_mengisi_urutan_setelah_nilai_terakhir(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $maks = (int) Statistic::query()->max('sort_order');

        $this->actingAs($admin);

        Livewire::test(CreateStatistic::class)
            ->assertSchemaStateSet(['sort_order' => $maks + 1]);
    }

    /**
     * Urutan item galeri dihitung per galeri: begitu galeri dipilih, form
     * langsung menawarkan nomor berikutnya pada galeri tersebut.
     */
    public function test_form_item_galeri_mengisi_urutan_berikutnya_pada_galeri_terpilih(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $gallery = Gallery::query()->orderBy('id')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(CreateGalleryItem::class)
            ->fillForm(['gallery_id' => $gallery->id])
            ->assertSchemaStateSet([
                'sort_order' => (int) $gallery->items()->max('sort_order') + 1,
            ]);
    }

    /**
     * Pita statistik Tentang memakai empat angka pertama menurut Urutan;
     * angka sisanya tampil pada kartu "Capaian lainnya".
     */
    public function test_pita_statistik_tentang_mengikuti_urutan(): void
    {
        $this->balikUrutan(Statistic::class);

        $xpath = $this->xpath('/tentang');

        $this->assertSame(
            Statistic::query()->active()->ordered()->take(4)->pluck('label')->all(),
            $this->teks($xpath, '//div[contains(@class, "stat-band")]//p[contains(@class, "stat-label")]'),
        );
    }

    /**
     * Tab Sejarah tampil menurut Urutan, bukan menurut tahun.
     */
    public function test_sejarah_tentang_mengikuti_urutan(): void
    {
        $this->balikUrutan(OrganizationHistory::class);

        $xpath = $this->xpath('/tentang');

        $this->assertSame(
            OrganizationHistory::query()->active()->ordered()->pluck('title')->all(),
            $this->teks($xpath, '//section[@id="sejarah"]//h3[contains(@class, "history-title")]'),
        );
    }

    /**
     * Kartu "Bidang Pengabdian Utama" mengikuti Urutan kategori program.
     */
    public function test_bidang_pengabdian_tentang_mengikuti_urutan(): void
    {
        $this->balikUrutan(ProgramCategory::class);

        $xpath = $this->xpath('/tentang');

        $this->assertSame(
            ProgramCategory::query()->active()->ordered()->pluck('name')->all(),
            $this->teks($xpath, '//section[.//h2[contains(., "Bidang Pengabdian Utama")]]//h3'),
        );
    }

    /**
     * Halaman Program: kartu pertama yang ditampilkan besar (featured) dan
     * kartu berikutnya mengikuti Urutan program.
     */
    public function test_daftar_program_mengikuti_urutan(): void
    {
        $this->balikUrutan(Program::class);

        $xpath = $this->xpath('/program');

        $this->assertSame(
            Program::query()->published()->ordered()->take(9)->pluck('title')->all(),
            $this->teks($xpath, '//article[contains(concat(" ", normalize-space(@class), " "), " card ")]//h3'),
        );
    }

    /**
     * Daftar album galeri mengikuti Urutan.
     */
    public function test_daftar_galeri_mengikuti_urutan(): void
    {
        $this->balikUrutan(Gallery::class);

        $xpath = $this->xpath('/galeri');

        $diharapkan = Gallery::query()->active()->ordered()->take(9)->pluck('title')->all();

        $this->assertSame(
            $diharapkan,
            array_slice(
                $this->teks($xpath, '//article[contains(concat(" ", normalize-space(@class), " "), " card ")]//h3'),
                0,
                count($diharapkan),
            ),
        );
    }

    /**
     * Daftar video mengikuti Urutan.
     */
    public function test_daftar_video_mengikuti_urutan(): void
    {
        $this->balikUrutan(Video::class);

        $xpath = $this->xpath('/galeri/video');

        $this->assertSame(
            Video::query()->published()->ordered()->take(9)->pluck('title')->all(),
            $this->teks($xpath, '//article//h3'),
        );
    }

    /**
     * Daftar wilayah mengikuti Urutan; wilayah dikelompokkan per pulau sesuai
     * urutan kemunculan provinsi pertamanya.
     */
    public function test_daftar_wilayah_mengikuti_urutan(): void
    {
        $this->balikUrutan(Region::class);

        $xpath = $this->xpath('/di-indonesia');

        $diharapkan = Region::query()
            ->active()
            ->whereNull('parent_id')
            ->ordered()
            ->get()
            ->groupBy(fn (Region $region): string => $region->island ?: 'Lainnya')
            ->flatMap(fn ($provinsi) => $provinsi->pluck('name'))
            ->values()
            ->all();

        $this->assertSame(
            $diharapkan,
            $this->teks($xpath, '//article//h3//a'),
        );
    }

    /**
     * Organisasi pada halaman detail wilayah tampil menurut Urutan.
     */
    public function test_organisasi_pada_detail_wilayah_mengikuti_urutan(): void
    {
        $this->balikUrutan(Organization::class);

        $region = Region::query()
            ->withCount('organizations')
            ->get()
            ->first(fn (Region $region): bool => $region->organizations_count > 1);

        $this->assertNotNull($region, 'Tidak ada wilayah dengan lebih dari satu organisasi pada data seeder.');

        $xpath = $this->xpath('/di-indonesia/'.$region->slug);

        $this->assertSame(
            $region->organizations()->active()->ordered()->pluck('name')->all(),
            $this->teks($xpath, '//section[.//h2[normalize-space(.)="Organisasi"]]//h3'),
        );
    }

    /**
     * Urutan pada basis data adalah dasar seluruh perbandingan: helper ini
     * menirukan susunan kolom Urutan dengan id sebagai penentu seri.
     *
     * @param  class-string<Model>  $model
     */
    private function urutkanMenurutUrutan(string $model): Builder
    {
        return $model::query()->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Balik nomor urut seluruh record agar susunannya berbeda dari urutan id
     * (urutan bawaan tabel dan data seeder), sehingga uji benar-benar menguji
     * kolom Urutan dan bukan kebetulan data.
     *
     * @param  class-string<Model>  $model
     */
    private function balikUrutan(string $model): void
    {
        $records = $model::query()->orderBy('id')->get();
        $total = $records->count();

        foreach ($records as $index => $record) {
            $record->update(['sort_order' => $total - $index]);
        }
    }

    private function xpath(string $url): DOMXPath
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->get($url)->assertOk()->getContent());
        libxml_clear_errors();

        return new DOMXPath($dom);
    }

    /**
     * Seluruh teks elemen hasil kueri, berurutan sesuai dokumen.
     *
     * @return list<string>
     */
    private function teks(DOMXPath $xpath, string $query): array
    {
        $hasil = [];

        $nodes = $xpath->query($query);

        $this->assertNotFalse($nodes, "Kueri XPath tidak valid: {$query}");

        foreach ($nodes as $node) {
            $hasil[] = trim((string) preg_replace('/\s+/u', ' ', $node->textContent));
        }

        return $hasil;
    }
}
