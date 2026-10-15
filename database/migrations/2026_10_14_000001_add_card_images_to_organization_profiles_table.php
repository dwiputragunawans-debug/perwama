<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gambar untuk tiga kartu bagian "Tentang kami" pada beranda (Tentang Kami,
     * Visi, Misi).
     *
     * Gambar disimpan pada baris profil organisasi yang sama dengan isi visi &
     * misi, sehingga pengelola menyunting teks dan fotonya dari satu tempat
     * (Tentang → Profil Organisasi).
     */
    public function up(): void
    {
        Schema::table('organization_profiles', function (Blueprint $table) {
            $table->string('about_card_image')->nullable()->after('mission');
            $table->string('vision_card_image')->nullable()->after('about_card_image');
            $table->string('mission_card_image')->nullable()->after('vision_card_image');
        });
    }

    public function down(): void
    {
        Schema::table('organization_profiles', function (Blueprint $table) {
            $table->dropColumn(['about_card_image', 'vision_card_image', 'mission_card_image']);
        });
    }
};
