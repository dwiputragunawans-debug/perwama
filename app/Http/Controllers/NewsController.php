<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\OrganizationProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Halaman "Berita" — berita, artikel, opini, dan pengumuman.
     */
    public function index(Request $request): View
    {
        $category = $request->query('kategori');
        $type = $request->query('tipe');
        $search = $request->query('q');

        return view('pages.news-index', [
            'profile' => OrganizationProfile::current(),
            'categories' => News::CATEGORIES,
            'types' => News::TYPES,
            'activeCategory' => $category,
            'activeType' => $type,
            'search' => $search,
            'featured' => News::query()->published()->where('is_featured', true)->ordered()->first(),
            'newsItems' => News::query()
                ->published()
                ->with('region')
                ->when($category, fn ($query) => $query->where('category', $category))
                ->when($type, fn ($query) => $query->where('type', $type))
                ->when($search, fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
                ->ordered()
                ->paginate(9)
                ->withQueryString(),
        ]);
    }

    /**
     * Detail berita.
     */
    public function show(string $slug): View
    {
        $news = News::query()
            ->published()
            ->where('slug', $slug)
            ->with(['region', 'program', 'tags', 'seoMetadata', 'publicMedia'])
            ->firstOrFail();

        $news->increment('views');

        return view('pages.news-show', [
            'profile' => OrganizationProfile::current(),
            'news' => $news,
            'seo' => $news->seoMetadata,
            'media' => $news->publicMedia,
            'related' => News::query()
                ->published()
                ->whereKeyNot($news->getKey())
                ->ordered()
                ->take(3)
                ->get(),
        ]);
    }
}
