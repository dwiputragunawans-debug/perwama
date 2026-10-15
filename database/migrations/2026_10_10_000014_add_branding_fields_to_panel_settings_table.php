<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah pengaturan branding panel: teks nama, slogan, dan favicon.
     * Ketiganya disatukan dengan pengaturan logo pada satu halaman.
     */
    public function up(): void
    {
        Schema::table('panel_settings', function (Blueprint $table) {
            $table->string('brand_name')->nullable()->after('brand_logo_height');
            $table->string('brand_tagline')->nullable()->after('brand_name');
            $table->string('favicon')->nullable()->after('brand_tagline');
        });
    }

    public function down(): void
    {
        Schema::table('panel_settings', function (Blueprint $table) {
            $table->dropColumn(['brand_name', 'brand_tagline', 'favicon']);
        });
    }
};
