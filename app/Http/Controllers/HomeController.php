<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\News;
use App\Models\OrganizationProfile;
use App\Models\Partner;
use App\Models\Region;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Beranda: hero, profil singkat, sebaran wilayah, berita, dan ajakan
     * bergabung.
     */
    public function __invoke(): View
    {
        return view('pages.home', [
            'profile' => OrganizationProfile::current(),
            'provinces' => Region::query()->active()->provinces()->ordered()->get(),
            'regionTotal' => Region::query()->active()->count(),
            // Satu berita unggulan + tiga kartu ringkas pada bagian "Berita".
            'newsItems' => News::query()->published()->ordered()->take(4)->get(),
            // Cover galeri dipakai sebagai cadangan slide hero bila gambar hero belum diunggah.
            'galleries' => Gallery::query()->active()->with('albumCategory')->ordered()->take(6)->get(),
            // Mitra kerja sama: logo dan tautan yang aktif di panel Partners.
            'partners' => Partner::query()->public()->ordered()->get(),
        ]);
    }
}
