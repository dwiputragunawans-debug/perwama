<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gambar pendukung bagian Identitas Organisasi, misalnya lembar partitur
     * Mars PERWAMA INA. Disimpan sebagai daftar path pada satu kolom JSON —
     * pola yang sama dengan `website_settings.hero_images` — karena bagian ini
     * berupa data tunggal dan urutan gambar cukup diatur lewat panel admin.
     */
    public function up(): void
    {
        Schema::table('organization_identities', function (Blueprint $table) {
            $table->json('images')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('organization_identities', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
