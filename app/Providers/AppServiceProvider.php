<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Organization;
use App\Models\OrganizationHistory;
use App\Models\OrganizationIdentity;
use App\Models\OrganizationProfile;
use App\Models\PanelSetting;
use App\Models\Partner;
use App\Models\PartnershipRequest;
use App\Models\Program;
use App\Models\Publication;
use App\Models\Region;
use App\Models\Statistic;
use App\Models\Story;
use App\Models\User;
use App\Models\Video;
use App\Models\WebsiteSetting;
use App\Observers\AuditObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Pembatasan laju formulir publik.
         *
         * Validasi dan token CSRF tidak menghentikan bot yang mengambil halaman
         * lebih dulu, sehingga endpoint anonim berikut perlu batas per alamat
         * IP. Batas dibuat berlapis (jangka pendek dan harian) agar lonjakan
         * singkat tertahan tanpa memblokir pengunjung yang memperbaiki isian
         * formulirnya beberapa kali. Nama limiter dipakai pada routes/web.php.
         */
        RateLimiter::for('formulir-kontak', fn (Request $request) => [
            Limit::perMinutes(10, 5)->by($request->ip()),
            Limit::perDay(25)->by($request->ip()),
        ]);

        RateLimiter::for('formulir-gabung', fn (Request $request) => [
            Limit::perHour(5)->by($request->ip()),
            Limit::perDay(15)->by($request->ip()),
        ]);

        /*
         * Telemetri lama kunjungan dikirim sekali per halaman; batas ini hanya
         * untuk mencegah penyalahgunaan, bukan untuk membatasi pembacaan.
         */
        RateLimiter::for('telemetri', fn (Request $request) => [
            Limit::perMinute(60)->by($request->ip()),
        ]);

        // Penyegar token CSRF: beberapa kali per menit sudah sangat cukup.
        RateLimiter::for('sesi-segar', fn (Request $request) => [
            Limit::perMinute(15)->by($request->ip()),
        ]);

        /*
         * Log aktivitas panel (menu Pengaturan → Log Aktivitas).
         *
         * Observer mencatat create/update/delete yang dilakukan pengguna yang
         * login ke tabel `audit_logs`, sehingga resource tersebut berisi jejak
         * perubahan sungguhan, bukan formulir kosong. Model yang paling sering
         * berubah — konten, pengguna, pendaftaran, dan pengaturan — dipantau;
         * tabel log itu sendiri tidak ikut dipantau agar tidak berulang.
         */
        $terpantau = [
            News::class,
            Program::class,
            Activity::class,
            Event::class,
            Story::class,
            Publication::class,
            Video::class,
            Gallery::class,
            GalleryItem::class,
            Region::class,
            Organization::class,
            Partner::class,
            Member::class,
            MembershipApplication::class,
            PartnershipRequest::class,
            ContactMessage::class,
            User::class,
            OrganizationProfile::class,
            OrganizationIdentity::class,
            OrganizationHistory::class,
            WebsiteSetting::class,
            PanelSetting::class,
            MenuItem::class,
            Statistic::class,
        ];

        foreach ($terpantau as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
