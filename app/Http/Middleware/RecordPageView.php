<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Support\VisitorFingerprint;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencatat kunjungan halaman publik ke tabel `page_views`.
 *
 * Angka pada kartu "Ringkasan Pengunjung Website" di dasbor panel dihitung dari
 * tabel tersebut. Yang dicatat hanya tampilan halaman yang benar-benar dibuka
 * pengunjung situs:
 *
 * - hanya permintaan GET yang berakhir dengan status 200 dan berjenis HTML;
 * - panel admin, endpoint Livewire, dan penyegar sesi diabaikan;
 * - pengelola yang sedang masuk (mis. menyunting sambil melihat situs) tidak
 *   dihitung sebagai pengunjung;
 * - alamat IP tidak disimpan, melainkan sidik jari berupa hash SHA-256 dari
 *   IP + user agent, sehingga pengunjung unik tetap dapat dihitung tanpa
 *   menyimpan data pribadi.
 */
class RecordPageView
{
    /**
     * Pola URL yang tidak dihitung sebagai kunjungan portal publik.
     */
    private const DIABAIKAN = [
        'admin',
        'admin/*',
        'livewire/*',
        'sesi/*',
        'up',
        'jejak-kunjungan',
    ];

    /**
     * Penanda user agent yang diabaikan (perayap mesin pencari, pemantau, dsb.)
     */
    private const POLA_PERAYAP = [
        'bot', 'crawler', 'spider', 'slurp', 'curl', 'wget', 'monitor', 'preview',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Pencatatan sengaja dilakukan pada `terminate()`, bukan di dalam
     * `handle()`: Laravel memanggilnya setelah respons dikirim ke pengunjung,
     * sehingga menyimpan satu baris kunjungan tidak menambah waktu muat halaman.
     */
    public function terminate(Request $request, Response $response): void
    {
        if ($this->dapatDicatat($request, $response)) {
            $this->catat($request);
        }
    }

    private function dapatDicatat(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return false;
        }

        if ($request->is(...self::DIABAIKAN) || $request->ajax()) {
            return false;
        }

        if (! str_contains((string) $response->headers->get('content-type'), 'text/html')) {
            return false;
        }

        return ! auth()->check() && ! $this->adalahPerayap($request);
    }

    private function catat(Request $request): void
    {
        PageView::create([
            'path' => '/'.ltrim($request->path(), '/'),
            'route_name' => $request->route()?->getName(),
            'visitor_hash' => VisitorFingerprint::of($request),
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'referrer' => $this->sumberKunjungan($request),
            'viewed_at' => now(),
        ]);
    }

    /**
     * Hanya nama domain pengunjung sebelumnya yang disimpan, bukan URL penuh,
     * agar tidak ada data pribadi yang ikut tercatat.
     */
    private function sumberKunjungan(Request $request): ?string
    {
        $asal = (string) $request->headers->get('referer');

        if ($asal === '' || ! str_starts_with($asal, 'http')) {
            return null;
        }

        $domain = parse_url($asal, PHP_URL_HOST);

        return filled($domain) ? Str::limit($domain, 190, '') : null;
    }

    private function adalahPerayap(Request $request): bool
    {
        $userAgent = strtolower((string) $request->userAgent());

        if ($userAgent === '') {
            return false;
        }

        foreach (self::POLA_PERAYAP as $pola) {
            if (str_contains($userAgent, $pola)) {
                return true;
            }
        }

        return false;
    }
}
