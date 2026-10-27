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
                // Kartu wilayah menghitung DPD saja; PC tampil terpisah pada
                // bagian "Cabang" di halaman detail wilayah.
                'organizations' => fn ($query) => $query->active()->where('type', 'dpw'),
                'branches' => fn ($query) => $query->active(),
            ])
            ->ordered()
            ->get();

        return view('pages.region-index', [
            'profile' => OrganizationProfile::current(),
            'provinces' => $provinces,
            'regionsByIsland' => $provinces->groupBy(fn (Region $region) => $region->island ?: 'Lainnya'),
            'totals' => [
                'region' => Region::query()->active()->count(),
                'province' => Region::query()->active()->where('type', 'province')->count(),
                'organization' => Organization::query()->active()->count(),
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
