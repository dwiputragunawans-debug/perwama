<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tempat berlangsungnya peristiwa sejarah organisasi.
     *
     * Kartu sejarah pada halaman Tentang menampilkan kota peristiwa pada
     * lencana tahun (mis. "Tahun 1998 — Jakarta") serta pada baris lokasi,
     * sehingga pengelola dapat mengisi tempat tiap peristiwa dari panel
     * (Tentang → Sejarah).
     */
    public function up(): void
    {
        Schema::table('organization_histories', function (Blueprint $table) {
            $table->string('location')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('organization_histories', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
