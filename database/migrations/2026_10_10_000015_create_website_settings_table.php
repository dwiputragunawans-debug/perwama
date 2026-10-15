<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengaturan tampilan website publik. Saat ini dipakai untuk gambar hero
     * beranda (carousel latar penuh) yang diunggah khusus oleh pengelola.
     */
    public function up(): void
    {
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->json('hero_images')->nullable();
            $table->timestamps();
        });

        /*
         * Baris tunggal (singleton) langsung dibuat agar halaman pengaturan
         * selalu memiliki satu record untuk diedit.
         */
        DB::table('website_settings')->insert([
            'id' => 1,
            'hero_images' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('website_settings');
    }
};
