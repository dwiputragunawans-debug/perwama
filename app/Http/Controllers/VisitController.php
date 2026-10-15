<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use App\Support\VisitorFingerprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Menerima laporan lama kunjungan dari peramban pengunjung.
 *
 * Halaman yang dibuka pengunjung dicatat oleh
 * App\Http\Middleware\RecordPageView. Berapa lama pengunjung berada di halaman
 * itu hanya diketahui peramban, sehingga skrip pada situs publik
 * (`resources/js/app.js`) melaporkannya sekali ketika halaman ditinggalkan.
 * Nilai terbesar yang bertahan — bila pengunjung kembali ke tab lalu
 * meninggalkannya lagi — yang disimpan, karena itulah lama kunjungan sebenarnya.
 */
class VisitController extends Controller
{
    public function storeDuration(Request $request): JsonResponse
    {
        $data = $request->validate([
            // Dibatasi satu jam agar tab yang dibiarkan terbuka semalaman tidak
            // membuat rata-rata waktu akses menjadi tidak wajar.
            'duration' => ['required', 'integer', 'min:1', 'max:3600'],
        ]);

        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        $kunjungan = $sessionId === null
            ? null
            : PageView::query()->where('session_id', $sessionId)->latest('viewed_at')->first();

        /*
         * Sesi pengunjung dapat saja sudah berakhir (mis. halaman lama dibiarkan
         * terbuka), sehingga laporan dialihkan ke kunjungan terakhir pengunjung
         * yang sama dalam setengah jam terakhir.
         */
        $kunjungan ??= PageView::query()
            ->where('visitor_hash', VisitorFingerprint::of($request))
            ->where('viewed_at', '>=', now()->subMinutes(30))
            ->latest('viewed_at')
            ->first();

        if ($kunjungan === null) {
            return response()->json(['status' => 'diabaikan']);
        }

        $durasi = (int) $data['duration'];

        if ($durasi > (int) $kunjungan->duration_seconds) {
            $kunjungan->update(['duration_seconds' => $durasi]);
        }

        return response()->json(['status' => 'tercatat']);
    }
}
