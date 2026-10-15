<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\OrganizationProfile;
use App\Models\Video;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Halaman "Galeri" — album foto dan video.
     */
    public function index(Request $request): View
    {
        $album = $request->query('album');

        return view('pages.gallery-index', [
            'profile' => OrganizationProfile::current(),
            'albums' => Gallery::ALBUMS,
            'activeAlbum' => $album,
            'galleries' => Gallery::query()
                ->active()
                ->withCount('items')
                ->when($album, fn ($query) => $query->where('album_category', $album))
                ->ordered()
                ->paginate(9)
                ->withQueryString(),
            'videos' => Video::query()->published()->ordered()->take(6)->get(),
        ]);
    }

    /**
     * Detail album galeri.
     */
    public function show(string $slug): View
    {
        $gallery = Gallery::query()
            ->active()
            ->where('slug', $slug)
            ->with(['region', 'program', 'items' => fn ($q) => $q->orderBy('sort_order')])
            ->firstOrFail();

        return view('pages.gallery-show', [
            'profile' => OrganizationProfile::current(),
            'gallery' => $gallery,
            'others' => Gallery::query()
                ->active()
                ->whereKeyNot($gallery->getKey())
                ->ordered()
                ->take(4)
                ->get(),
        ]);
    }

    /**
     * Halaman daftar video.
     */
    public function videos(Request $request): View
    {
        $category = $request->query('kategori');

        return view('pages.video-index', [
            'profile' => OrganizationProfile::current(),
            'categories' => Video::CATEGORIES,
            'activeCategory' => $category,
            'videos' => Video::query()
                ->published()
                ->when($category, fn ($query) => $query->where('category', $category))
                ->ordered()
                ->paginate(9)
                ->withQueryString(),
        ]);
    }

    /**
     * Detail video.
     */
    public function showVideo(string $slug): View
    {
        $video = Video::query()->published()->where('slug', $slug)->firstOrFail();

        return view('pages.video-show', [
            'profile' => OrganizationProfile::current(),
            'video' => $video,
            'others' => Video::query()
                ->published()
                ->whereKeyNot($video->getKey())
                ->ordered()
                ->take(4)
                ->get(),
        ]);
    }
}
