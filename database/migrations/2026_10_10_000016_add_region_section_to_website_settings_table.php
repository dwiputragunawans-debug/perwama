<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Teks bagian "Wilayah" (sebaran wilayah) pada beranda. Judul dan
     * deskripsi disimpan di sini agar pengelola dapat menyuntingnya lewat
     * Pengaturan → Tampilan Website, bukan lagi tertanam di blade.
     */
    public function up(): void
    {
        Schema::table('website_settings', function (Blueprint $table) {
            $table->string('region_section_title')->nullable()->after('hero_images');
            $table->text('region_section_description')->nullable()->after('region_section_title');
        });

        /*
         * Isi teks bawaan pada baris tunggal agar halaman pengaturan langsung
         * menampilkan teks yang sedang tayang dan siap disunting.
         */
        DB::table('website_settings')->where('id', 1)->update([
            'region_section_title' => 'Jejaring perempuan Maluku di seluruh Nusantara',
            'region_section_description' => 'Dari Maluku hingga perantauan, PERWAMA INA hadir melalui wilayah, cabang, dan komunitas anggota yang tersebar di berbagai provinsi.',
        ]);
    }

    public function down(): void
    {
        Schema::table('website_settings', function (Blueprint $table) {
            $table->dropColumn(['region_section_title', 'region_section_description']);
        });
    }
};
