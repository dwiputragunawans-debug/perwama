<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tokoh organisasi pada halaman Tentang Kami.
     *
     * Setiap baris mewakili satu kartu tokoh (umumnya pendiri), lengkap dengan
     * foto, nama, jabatan/peran, urutan tampil, dan status aktif. Dipisahkan
     * dari tabel `managements` karena tokoh pendiri bukan bagian dari struktur
     * kepengurusan yang sedang menjabat.
     */
    public function up(): void
    {
        Schema::create('organization_figures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position')->default('Pendiri');
            $table->string('photo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_figures');
    }
};
