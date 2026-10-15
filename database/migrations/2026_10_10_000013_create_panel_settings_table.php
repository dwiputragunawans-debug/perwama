<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('brand_logo')->nullable();
            $table->string('brand_logo_height')->nullable()->default('2.5rem');
            $table->string('login_background')->nullable();
            $table->string('login_background_color')->nullable()->default('#152038');
            $table->timestamps();
        });

        /*
         * Baris tunggal (singleton) langsung dibuat agar halaman pengaturan
         * selalu memiliki satu record untuk diedit.
         */
        DB::table('panel_settings')->insert([
            'id' => 1,
            'brand_logo_height' => '2.5rem',
            'login_background_color' => '#152038',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_settings');
    }
};
