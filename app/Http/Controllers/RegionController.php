<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Organization;
use App\Models\OrganizationProfile;
use App\Models\Region;
use Illuminate\Contracts\View\View;

class RegionController extends Controller
{
    /**
     * Halaman "Wilayah" — sebaran wilayah kerja PERWAMA INA.
     */
    public function index(): View
    {
        $provinces = Region::query()
            ->active()
            ->whereNull('parent_id')
            ->withCount([
                // Kartu wilayah memakai pembagian yang sama dengan halaman
                // detail: DPD tampil sebagai "organisasi", PC sebagai "cabang".
                // Tabel branches (ranting) sengaja tidak dipakai agar angkanya
                // tidak berbeda dengan daftar PC di halaman detail.
                'organizations as dpw_count' => fn ($query) => $query->active()->where('type', 'dpw'),
                'organizations as pc_count' => fn ($query) => $query->active()->where('type', 'pc'),
            ])
            ->ordered()
            ->get();

        $regionsByIsland = $provinces->groupBy(fn (Region $region) => $region->island ?: 'Lainnya');

        return view('pages.region-index', [
            'profile' => OrganizationProfile::current(),
            'provinces' => $provinces,
            'regionsByIsland' => $regionsByIsland,
            'totals' => [
                // Jumlah wilayah mengikuti kelompok pulau yang benar-benar
                // dirender, bukan seluruh baris regions (termasuk kabupaten/kota).
                'region' => $regionsByIsland->count(),
                // Statistik atas memakai istilah yang sama dengan kartu:
                // DPD = organisasi tipe dpw, Cabang = organisasi tipe pc.
                'dpd' => Organization::query()->active()->where('type', 'dpw')->count(),
                'branch' => Organization::query()->active()->where('type', 'pc')->count(),
                'member' => Member::query()->active()->count(),
            ],
        ]);
    }

    /**
     * Detail satu wilayah beserta organisasi, kegiatan, dan cerita di dalamnya.
     */
    public function show(string $slug): View
    {
        $region = Region::query()
            ->active()
            ->where('slug', $slug)
            ->with([
                'parent',
                'children' => fn ($q) => $q->active()->ordered(),
                'organizations' => fn ($q) => $q->active()->ordered(),
                'activities' => fn ($q) => $q->published()->ordered()->take(6),
                'events' => fn ($q) => $q->upcoming()->take(4),
                'stories' => fn ($q) => $q->published()->ordered()->take(3),
                'seoMetadata',
                'publicMedia',
            ])
            ->firstOrFail();

        return view('pages.region-show', [
            'profile' => OrganizationProfile::current(),
            'region' => $region,
            'seo' => $region->seoMetadata,
            'media' => $region->publicMedia,
            'provinces' => Region::query()->active()->provinces()->ordered()->get(),
            'organizationGroups' => collect([
                [
                    'title' => 'Organisasi',
                    'organizations' => $region->organizations
                        ->reject(fn (Organization $organization): bool => $organization->type === 'pc')
                        ->values(),
                ],
                [
                    'title' => 'Cabang',
                    'organizations' => $region->organizations
                        ->where('type', 'pc')
                        ->values(),
                ],
            ])->reject(fn (array $group): bool => $group['organizations']->isEmpty()),
        ]);
    }
}
