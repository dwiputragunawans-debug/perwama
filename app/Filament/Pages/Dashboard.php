<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dasbor panel: ringkasan pengunjung situs publik dan ringkasan organisasi.
 *
 * Dasbor disusun dalam tiga kolom supaya kartu statistik dapat memakai lebar
 * penuh, sedangkan daftar "Pendaftaran Anggota Terbaru" dan grafik
 * "Pertumbuhan Anggota" tampil berdampingan (dua kolom berbanding satu kolom).
 * Halaman ini ditemukan otomatis lewat `discoverPages` pada AdminPanelProvider.
 */
class Dashboard extends BaseDashboard
{
    /**
     * Sebutan menu dalam bahasa Indonesia, menggantikan bawaan "Dashboard".
     */
    protected static ?string $navigationLabel = 'Dasbor';

    protected static ?string $title = 'Dasbor';

    /**
     * @return int|array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return 3;
    }
}
