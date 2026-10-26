<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan Soft Deletes pada tabel yang datanya perlu dapat dipulihkan.
 *
 * Kolom ditambahkan lewat migration baru agar database yang sudah berjalan
 * ikut berubah; migration awal tidak diubah. Tabel log, pivot, statistik
 * kunjungan, dan pesan kontak sengaja tidak termasuk: data tersebut lebih
 * tepat dipurge/dianonimkan sesuai kebijakan retensi.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const TABLES = [
        'users',
        'members',
        'membership_applications',
        'galleries',
        'gallery_items',
        'news',
        'stories',
        'activities',
        'events',
        'publications',
        'videos',
        'organization_profiles',
        'organization_histories',
        'organization_identities',
        'organization_figures',
        'partners',
        'programs',
        'regions',
        'organizations',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }
};
