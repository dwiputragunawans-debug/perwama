<?php

namespace App\Http\Controllers;

use App\Models\OrganizationProfile;
use App\Models\Publication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    /**
     * Halaman "Publikasi" — laporan, buku, modul, dan kajian.
     */
    public function index(Request $request): View
    {
        $category = $request->query('kategori');

        return view('pages.publication-index', [
            'profile' => OrganizationProfile::current(),
            'categories' => Publication::CATEGORIES,
            'activeCategory' => $category,
            'publications' => Publication::query()
                ->published()
                ->when($category, fn ($query) => $query->where('category', $category))
                ->ordered()
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    /**
     * Detail publikasi.
     */
    public function show(string $slug): View
    {
        $publication = Publication::query()
            ->published()
            ->where('slug', $slug)
            ->with('program')
            ->firstOrFail();

        return view('pages.publication-show', [
            'profile' => OrganizationProfile::current(),
            'publication' => $publication,
            'others' => Publication::query()
                ->published()
                ->whereKeyNot($publication->getKey())
                ->ordered()
                ->take(4)
                ->get(),
        ]);
    }
}
