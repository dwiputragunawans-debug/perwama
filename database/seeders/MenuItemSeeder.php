<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Menyiapkan menu navigasi website bawaan.
     *
     * `firstOrCreate` menjaga agar perubahan yang sudah dilakukan pengelola
     * (nama, urutan, atau status tampil) tidak tertimpa saat seeder dijalankan
     * ulang.
     */
    public function run(): void
    {
        foreach (MenuItem::DEFAULTS as $item) {
            MenuItem::query()->firstOrCreate(
                ['key' => $item['key']],
                [
                    'label' => $item['label'],
                    'route_name' => $item['route_name'],
                    'pattern' => $item['pattern'],
                    'is_active' => true,
                    'sort_order' => $item['sort_order'],
                ],
            );
        }

        MenuItem::flushNavigationCache();

        $this->command->info('Menu navigasi website berhasil disiapkan ('.MenuItem::query()->count().' menu).');
    }
}
