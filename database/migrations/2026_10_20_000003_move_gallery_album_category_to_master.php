<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Kategori bawaan yang sebelumnya ditulis langsung di form Galeri.
     *
     * Slug lama dipertahankan agar tautan publik `?album=...` tidak berubah.
     */
    private const DEFAULT_CATEGORIES = [
        'hut_perwama_ina' => 'HUT PERWAMA INA',
        'pelatihan' => 'Pelatihan',
        'bakti_sosial' => 'Bakti Sosial',
        'seminar' => 'Seminar',
        'rapat_organisasi' => 'Rapat Organisasi',
        'kunjungan' => 'Kunjungan',
        'kegiatan_daerah' => 'Kegiatan Daerah',
        'budaya' => 'Budaya',
    ];

    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->foreignId('album_category_id')
                ->nullable()
                ->constrained('album_categories')
                ->nullOnDelete();
        });

        $now = now();
        $sortOrder = 0;

        foreach (self::DEFAULT_CATEGORIES as $slug => $name) {
            DB::table('album_categories')->insertOrIgnore([
                'name' => $name,
                'slug' => $slug,
                'sort_order' => ++$sortOrder,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Kategori buatan pengelola yang belum ada di daftar bawaan tetap
        // dipertahankan sebagai master data.
        $legacyCategories = DB::table('galleries')
            ->whereNotNull('album_category')
            ->where('album_category', '<>', '')
            ->distinct()
            ->pluck('album_category');

        foreach ($legacyCategories as $slug) {
            if (array_key_exists($slug, self::DEFAULT_CATEGORIES)) {
                continue;
            }

            DB::table('album_categories')->insertOrIgnore([
                'name' => Str::headline($slug),
                'slug' => $slug,
                'sort_order' => ++$sortOrder,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $categoryIds = DB::table('album_categories')->pluck('id', 'slug');

        DB::table('galleries')
            ->select(['id', 'album_category'])
            ->whereNotNull('album_category')
            ->orderBy('id')
            ->chunkById(100, function ($galleries) use ($categoryIds): void {
                foreach ($galleries as $gallery) {
                    $categoryId = $categoryIds[$gallery->album_category] ?? null;

                    if ($categoryId) {
                        DB::table('galleries')
                            ->where('id', $gallery->id)
                            ->update(['album_category_id' => $categoryId]);
                    }
                }
            });

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn('album_category');
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->string('album_category')->nullable();
        });

        $categorySlugs = DB::table('album_categories')->pluck('slug', 'id');

        DB::table('galleries')
            ->select(['id', 'album_category_id'])
            ->whereNotNull('album_category_id')
            ->orderBy('id')
            ->chunkById(100, function ($galleries) use ($categorySlugs): void {
                foreach ($galleries as $gallery) {
                    $slug = $categorySlugs[$gallery->album_category_id] ?? null;

                    if ($slug) {
                        DB::table('galleries')
                            ->where('id', $gallery->id)
                            ->update(['album_category' => $slug]);
                    }
                }
            });

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('album_category_id');
        });
    }
};
