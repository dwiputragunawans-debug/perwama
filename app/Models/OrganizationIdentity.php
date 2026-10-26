<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Identitas organisasi pada halaman Tentang Kami.
 *
 * Berisi judul bagian (mis. "Mars PERWAMA INA"), deskripsi berupa lirik, serta
 * gambar pendukung seperti lembar partitur. Data disimpan sebagai satu baris
 * tunggal (singleton), sama seperti OrganizationProfile dan WebsiteSetting.
 */
class OrganizationIdentity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'images',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
        ];
    }

    /**
     * Cache data selama satu siklus permintaan.
     */
    protected static ?self $cached = null;

    /**
     * Ambil (atau buat) satu-satunya baris identitas organisasi.
     */
    public static function current(): self
    {
        if (static::$cached instanceof self) {
            return static::$cached;
        }

        /*
         * Identitas adalah data tunggal. Baris yang terlanjur di-soft-delete
         * tetap dibaca agar `firstOrCreate(['id' => 1])` tidak menabrak
         * primary key; admin dapat memulihkannya lewat filter data terhapus.
         */
        return static::$cached = static::withTrashed()->firstOrCreate(['id' => 1], [
            'title' => 'Mars PERWAMA INA',
        ]);
    }

    /**
     * Uraikan deskripsi menjadi bait berdasarkan baris kosong.
     *
     * Baris baru di dalam satu bait tetap dipertahankan agar pemenggalan lirik
     * yang ditulis pengelola tidak berubah saat ditampilkan.
     *
     * @return array<int, string>
     */
    public function stanzas(): array
    {
        $description = trim(str_replace(["\r\n", "\r"], "\n", (string) $this->description));

        if ($description === '') {
            return [];
        }

        $stanzas = preg_split('/\n\s*\n/', $description) ?: [];

        return array_values(array_filter(
            array_map(static fn (string $stanza): string => trim($stanza), $stanzas),
            static fn (string $stanza): bool => $stanza !== '',
        ));
    }

    /**
     * Kredit pencipta bila bait pertama diawali "Ciptaan:".
     *
     * Ditampilkan terpisah di bawah judul agar tidak menyatu dengan lirik.
     */
    public function credit(): ?string
    {
        $first = $this->stanzas()[0] ?? null;

        return $first !== null && preg_match('/^Ciptaan\s*:/i', $first) ? $first : null;
    }

    /**
     * Bait lirik tanpa baris kredit pencipta.
     *
     * @return array<int, string>
     */
    public function verses(): array
    {
        $stanzas = $this->stanzas();

        if ($this->credit() !== null) {
            array_shift($stanzas);
        }

        return $stanzas;
    }

    /**
     * Gambar pendukung (hasil unggahan) sebagai URL siap pakai.
     *
     * Urutan array mengikuti urutan yang diatur pengelola di panel admin,
     * sehingga lembar partitur tampil berurutan.
     *
     * @return Collection<int, string>
     */
    public function imageUrls(): Collection
    {
        return collect($this->images ?? [])
            ->map(fn (?string $path): ?string => media_url($path))
            ->filter()
            ->values();
    }

    /**
     * Bersihkan cache setelah data diubah.
     */
    public static function flushCache(): void
    {
        static::$cached = null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
        static::restored(fn () => static::flushCache());
    }
}
