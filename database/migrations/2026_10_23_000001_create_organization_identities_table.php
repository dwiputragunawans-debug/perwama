<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Identitas organisasi pada halaman Tentang Kami: judul dan deskripsi
     * berupa lirik Mars PERWAMA INA. Data tunggal langsung dibuat agar bagian
     * tersebut tampil setelah migrasi dan dapat disunting lewat panel admin.
     */
    public function up(): void
    {
        Schema::create('organization_identities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->timestamps();
        });

        DB::table('organization_identities')->insert([
            'id' => 1,
            'title' => 'Mars PERWAMA INA',
            'description' => <<<'LYRICS'
Ciptaan: Izzak Bakar

Persatuan Wanita Maluku Bersatu, hidup dan berpadu
Bersama berjuang bahu membahu
Di dalam iman yang teguh serta bekerja dan berdoa,
Agar s'lalu tetap setia
Mengemban tugas yang maha mulia
Menolong sesama manusia.

Persatuan Wanita Maluku Bersatu, hidup dan berpadu
Dan bersama berjuang dalam bakti
Serta tak mengenal letih
Melangkah dengan yakin pasti berkat rahmat yang Tuhan b'ri
Hingga PERWAMA s'lalu mengabdi
Dalam cinta kasih sejati

Ref. (2x)

Majulah bersama
Persatuan Wanita Maluku
Bertekun dan berjuang selamanya
Melayani Tuhan dan sesamanya
LYRICS,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_identities');
    }
};
