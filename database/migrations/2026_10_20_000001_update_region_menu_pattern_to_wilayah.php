<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * URL halaman wilayah berubah dari `/di-indonesia` menjadi `/wilayah`.
 * Perbarui pola menu tersimpan agar penanda menu aktif tetap bekerja.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->gantiPola('di-indonesia', 'wilayah');
    }

    public function down(): void
    {
        $this->gantiPola('wilayah', 'di-indonesia');
    }

    private function gantiPola(string $lama, string $baru): void
    {
        DB::table('menu_items')
            ->where('route_name', 'region.index')
            ->where('pattern', 'like', '%'.$lama.'%')
            ->update([
                'pattern' => DB::raw(
                    "REPLACE(pattern, '".$lama."', '".$baru."')",
                ),
            ]);
    }
};
