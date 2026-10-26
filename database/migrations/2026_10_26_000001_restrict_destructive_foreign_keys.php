<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Database yang sudah berjalan masih memiliki dua foreign key CASCADE dari
 * migration lama. Mengubah file migration awal tidak mengubah schema yang
 * sudah terpasang, sehingga migration ini mengganti constraint tersebut.
 *
 * SQLite dilewati karena tidak mendukung drop/add foreign key di tempat;
 * database uji selalu dibuat ulang dari migration awal yang sudah memakai
 * RESTRICT.
 */
return new class extends Migration
{
    /**
     * @var array<int, array{table: string, column: string, references: string}>
     */
    private const CONSTRAINTS = [
        ['table' => 'gallery_items', 'column' => 'gallery_id', 'references' => 'galleries'],
        ['table' => 'taggables', 'column' => 'tag_id', 'references' => 'tags'],
    ];

    public function up(): void
    {
        $this->replaceConstraints('restrict');
    }

    public function down(): void
    {
        // Sengaja tidak mengembalikan CASCADE: rollback tidak boleh
        // mengaktifkan kembali penghapusan data anak secara otomatis.
    }

    private function replaceConstraints(string $onDelete): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach (self::CONSTRAINTS as $constraint) {
            Schema::table($constraint['table'], function (Blueprint $table) use ($constraint): void {
                $table->dropForeign([$constraint['column']]);
            });

            Schema::table($constraint['table'], function (Blueprint $table) use ($constraint, $onDelete): void {
                $table->foreign($constraint['column'])
                    ->references('id')
                    ->on($constraint['references'])
                    ->onDelete($onDelete);
            });
        }
    }
};
