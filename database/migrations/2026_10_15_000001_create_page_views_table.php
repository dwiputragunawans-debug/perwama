<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kunjungan halaman portal publik.
     *
     * Tabel ini menjadi sumber angka pada bagian "Ringkasan Pengunjung
     * Website" di dasbor panel: pengunjung unik, total halaman dilihat,
     * rata-rata waktu akses, dan grafik trafik harian. Satu baris mewakili
     * satu halaman yang dibuka seorang pengunjung (lihat
     * App\Http\Middleware\RecordPageView).
     *
     * `visitor_hash` adalah hash SHA-256 dari alamat IP + user agent, sehingga
     * pengunjung unik tetap dapat dihitung tanpa menyimpan data pribadi.
     * `session_id` menghubungkan laporan lama kunjungan dari peramban dengan
     * baris ini, sedangkan `duration_seconds` menyimpan berapa lama pengunjung
     * berada di halaman tersebut.
     */
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('route_name')->nullable();
            $table->string('visitor_hash', 64);
            $table->string('session_id')->nullable();
            $table->string('referrer')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->index('visitor_hash');
            $table->index('session_id');
            $table->index('viewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
