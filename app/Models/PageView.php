<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu kunjungan halaman portal publik.
 *
 * Baris dibuat oleh App\Http\Middleware\RecordPageView saat pengunjung membuka
 * halaman situs, lalu dilengkapi `duration_seconds` oleh laporan peramban
 * (lihat App\Http\Controllers\VisitController). Angka pada kartu "Ringkasan
 * Pengunjung Website" dihitung dari tabel ini lewat App\Support\VisitorStatistics.
 */
class PageView extends Model
{
    protected $fillable = [
        'path', 'route_name', 'visitor_hash', 'session_id', 'referrer',
        'duration_seconds', 'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'viewed_at' => 'datetime',
        ];
    }

    /**
     * Batasi ke kunjungan pada rentang waktu tertentu.
     */
    public function scopeBetween(Builder $query, mixed $start, mixed $end): Builder
    {
        return $query->whereBetween('viewed_at', [$start, $end]);
    }
}
