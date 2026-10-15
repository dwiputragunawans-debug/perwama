<?php

namespace App\Http\Controllers;

use App\Models\OrganizationProfile;
use App\Models\Story;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    /**
     * Halaman "Cerita" — kisah inspiratif perempuan Maluku.
     */
    public function index(Request $request): View
    {
        $category = $request->query('kategori');

        return view('pages.story-index', [
            'profile' => OrganizationProfile::current(),
            'categories' => Story::CATEGORIES,
            'activeCategory' => $category,
            'featured' => Story::query()->published()->where('is_featured', true)->ordered()->first(),
            'stories' => Story::query()
                ->published()
                ->with('region')
                ->when($category, fn ($query) => $query->where('category', $category))
                ->ordered()
                ->paginate(9)
                ->withQueryString(),
        ]);
    }

    /**
     * Detail cerita.
     */
    public function show(string $slug): View
    {
        $story = Story::query()
            ->published()
            ->where('slug', $slug)
            ->with(['region', 'organization', 'program', 'tags'])
            ->firstOrFail();

        $story->increment('views');

        return view('pages.story-show', [
            'profile' => OrganizationProfile::current(),
            'story' => $story,
            'related' => Story::query()
                ->published()
                ->whereKeyNot($story->getKey())
                ->ordered()
                ->take(3)
                ->get(),
        ]);
    }
}
