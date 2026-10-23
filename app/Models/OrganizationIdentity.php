<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Identitas organisasi pada halaman Tentang Kami.
 *
 * Berisi judul bagian (mis. "Mars PERWAMA INA") dan deskripsi berupa lirik.
 * Data disimpan sebagai satu baris tunggal (singleton), sama seperti
 * OrganizationProfile dan WebsiteSetting.
 */
class OrganizationIdentity extends Model
{
    protected $fillable = [
        'title', 'description',
    ];

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

        return static::$cached = static::query()->firstOrCreate(['id' => 1], [
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
    }
}
