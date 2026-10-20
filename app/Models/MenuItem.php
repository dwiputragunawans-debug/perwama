<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class MenuItem extends Model
{
    /**
     * Menu navigasi bawaan situs. Dipakai saat tabel menu belum diisi dan
     * sebagai acuan seeder, sehingga susunan menu tetap tersedia tanpa
     * bergantung pada data yang tersimpan di basis data.
     *
     * @var list<array{key: string, label: string, route_name: string, pattern: string, sort_order: int}>
     */
    public const DEFAULTS = [
        ['key' => 'home', 'label' => 'Beranda', 'route_name' => 'home', 'pattern' => '/', 'sort_order' => 1],
        ['key' => 'about', 'label' => 'Tentang', 'route_name' => 'about', 'pattern' => 'tentang', 'sort_order' => 2],
        ['key' => 'program', 'label' => 'Program', 'route_name' => 'program.index', 'pattern' => 'program*', 'sort_order' => 3],
        ['key' => 'region', 'label' => 'Wilayah', 'route_name' => 'region.index', 'pattern' => 'wilayah*', 'sort_order' => 4],
        ['key' => 'activity', 'label' => 'Kegiatan', 'route_name' => 'activity.index', 'pattern' => 'kegiatan*', 'sort_order' => 5],
        ['key' => 'story', 'label' => 'Cerita', 'route_name' => 'story.index', 'pattern' => 'cerita*', 'sort_order' => 6],
        ['key' => 'news', 'label' => 'Berita', 'route_name' => 'news.index', 'pattern' => 'berita*', 'sort_order' => 7],
        ['key' => 'gallery', 'label' => 'Galeri', 'route_name' => 'gallery.index', 'pattern' => 'galeri*', 'sort_order' => 8],
        ['key' => 'contact', 'label' => 'Kontak', 'route_name' => 'contact', 'pattern' => 'kontak', 'sort_order' => 9],
    ];

    /**
     * Susunan kelompok pada navigasi situs publik: sembilan menu pada DEFAULTS
     * dirapikan menjadi lima menu utama — Beranda, Profil (Tentang Kami +
     * Wilayah), Program & Kegiatan (Program + Kegiatan + Cerita), Publikasi
     * (Berita + Galeri), dan Kontak.
     *
     * Tiap kelompok menyebut kunci menu (`menu_items.key`) yang menjadi isinya.
     * Kelompok bertaut tunggal memakai nama menunya sendiri sehingga tetap
     * mengikuti penyesuaian pengelola; kelompok berisi beberapa menu tampil
     * sebagai menu tarik-turun memakai nama kelompok di sini.
     *
     * Daftar ini sengaja dipisah dari DEFAULTS: DEFAULTS adalah sembilan bagian
     * konten yang sekaligus menjadi grup sidebar panel admin, sedangkan GROUPS
     * hanya mengatur bagaimana menu-menu itu dikelompokkan pada header website.
     *
     * @var list<array{label: string|null, items: list<string>}>
     */
    public const GROUPS = [
        ['label' => null, 'items' => ['home']],
        ['label' => 'Profil', 'items' => ['about', 'region']],
        ['label' => 'Program & Kegiatan', 'items' => ['program', 'activity', 'story']],
        ['label' => 'Publikasi', 'items' => ['news', 'gallery']],
        ['label' => null, 'items' => ['contact']],
    ];

    /**
     * Daftar rute yang boleh dipakai sebagai tujuan menu, dipetakan ke label
     * halaman agar mudah dipilih pada panel admin.
     *
     * @var array<string, string>
     */
    public const ROUTES = [
        'home' => 'Beranda',
        'about' => 'Tentang Kami',
        'program.index' => 'Program',
        'region.index' => 'Wilayah',
        'activity.index' => 'Kegiatan',
        'story.index' => 'Cerita Perempuan',
        'news.index' => 'Berita',
        'publication.index' => 'Publikasi',
        'gallery.index' => 'Galeri',
        'video.index' => 'Video',
        'join' => 'Bergabung',
        'contact' => 'Kontak',
    ];

    /**
     * Cache menu per siklus permintaan agar navigasi tidak di-query berulang.
     *
     * @var list<array{label: string, route: string, pattern: string}>|null
     */
    protected static ?array $cached = null;

    protected $fillable = [
        'key', 'label', 'route_name', 'pattern', 'is_active', 'sort_order',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Menu yang ditampilkan pada navigasi situs publik, sudah dikelompokkan
     * mengikuti GROUPS (lima menu utama).
     *
     * Susunan menu di dalam tiap kelompok — dan posisi kelompoknya — mengikuti
     * kolom Urutan pada panel admin, bukan urutan kunci pada GROUPS.
     *
     * Hanya menu aktif dan berute valid yang dipakai. Bila tabel menu belum
     * pernah diisi, menu bawaan dipakai; bila seluruh menu dimatikan oleh
     * pengelola, hasilnya boleh kosong (bukan kembali ke bawaan). Menu yang
     * tidak disebut pada GROUPS (mis. ditambahkan sendiri lewat panel) tetap
     * dikembalikan sebagai tautan tersendiri agar tidak hilang dari navigasi.
     *
     * `route` bernilai null pada kelompok menu tarik-turun: labelnya berupa
     * tombol pembuka, bukan tautan. `pattern` boleh berisi beberapa pola sekaligus
     * (daftar) untuk menandai halaman aktif di dalam kelompok.
     *
     * @return list<array{
     *     label: string,
     *     route: string|null,
     *     pattern: string|list<string>,
     *     items: list<array{label: string, route: string, pattern: string}>
     * }>
     */
    public static function navigation(): array
    {
        if (static::$cached !== null) {
            return static::$cached;
        }

        $items = static::query()->ordered()->get();

        if ($items->isEmpty()) {
            $items = collect(static::DEFAULTS)
                ->map(fn (array $item): self => new self($item));
        }

        $items = $items
            ->filter(fn (self $item): bool => $item->is_active !== false)
            ->filter(fn (self $item): bool => filled($item->route_name) && Route::has($item->route_name))
            ->keyBy('key');

        $kelompok = [];

        foreach (static::GROUPS as $grup) {
            /*
             * Kolom Urutan pada panel admin menentukan susunan menu di dalam
             * kelompok. Kunci pada GROUPS hanya menentukan menu mana yang
             * sekelompok, bukan urutan tampilnya.
             */
            $anggota = collect($grup['items'])
                ->map(fn (string $kunci): ?self => $items->get($kunci))
                ->filter()
                ->sortBy(fn (self $item): int => (int) $item->sort_order)
                ->values();

            if ($anggota->isEmpty()) {
                continue;
            }

            $tautan = $anggota
                ->map(fn (self $item): array => static::navigationItem($item))
                ->all();

            /*
             * Kelompok bertaut tunggal (Beranda, Kontak) tampil sebagai tautan
             * biasa dan memakai nama menunya, bukan nama kelompok.
             */
            $kelompok[] = count($tautan) === 1 && $grup['label'] === null
                ? [...$tautan[0], 'items' => [], '_urutan' => (int) $anggota->first()->sort_order]
                : [
                    'label' => $grup['label'],
                    'route' => null,
                    'pattern' => array_column($tautan, 'pattern'),
                    'items' => $tautan,
                    '_urutan' => (int) $anggota->min('sort_order'),
                ];
        }

        $disebut = collect(static::GROUPS)
            ->flatMap(fn (array $grup): array => $grup['items'])
            ->all();

        /*
         * Menu yang tidak disebut pada GROUPS (mis. menu tambahan dari panel)
         * tetap tampil sebagai tautan tunggal agar tidak pernah hilang.
         *
         * Penyaringan memakai kunci menu, bukan Collection::except(): pada
         * koleksi Eloquent, except() membandingkan primary key (id) sehingga
         * nama kunci menu tidak akan pernah cocok.
         */
        $sisa = $items->reject(fn (self $item): bool => in_array($item->key, $disebut, true));

        foreach ($sisa as $item) {
            $kelompok[] = [...static::navigationItem($item), 'items' => [], '_urutan' => (int) $item->sort_order];
        }

        /*
         * Urutan kelompok pun mengikuti kolom Urutan: kelompok yang memuat menu
         * bernomor terkecil tampil lebih dulu. `_urutan` hanya dipakai untuk
         * pengurutan dan tidak ikut dikembalikan ke header.
         */
        return static::$cached = collect($kelompok)
            ->sortBy('_urutan')
            ->map(function (array $grup): array {
                unset($grup['_urutan']);

                return $grup;
            })
            ->values()
            ->all();
    }

    /**
     * Satu menu dalam bentuk yang dipakai header situs: nama, tujuan, dan pola
     * penanda halaman aktif.
     *
     * @return array{label: string, route: string, pattern: string}
     */
    protected static function navigationItem(self $item): array
    {
        return [
            'label' => $item->label,
            'route' => $item->route_name,
            'pattern' => $item->pattern,
        ];
    }

    /**
     * Bersihkan cache menu, misalnya setelah perubahan dari seeder/panel.
     */
    public static function flushNavigationCache(): void
    {
        static::$cached = null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushNavigationCache());
        static::deleted(fn () => static::flushNavigationCache());
    }
}
