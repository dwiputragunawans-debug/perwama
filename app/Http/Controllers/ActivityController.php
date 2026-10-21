<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\OrganizationProfile;
use App\Models\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Halaman "Kegiatan" — agenda mendatang dan kegiatan yang telah terlaksana.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'kegiatan');
        $category = $request->query('kategori');

        return view('pages.activity-index', [
            'profile' => OrganizationProfile::current(),
            'tab' => $tab,
            'activeCategory' => $category,
            'categories' => Activity::CATEGORIES,
            'agendas' => Event::query()->upcoming()->take(6)->get(),
            'pastEvents' => Event::query()->where('status', 'completed')->ordered()->take(6)->get(),
            'activities' => Activity::query()
                ->published()
                ->with(['region', 'program'])
                ->when($category, fn ($query) => $query->where('category', $category))
                ->ordered()
                ->paginate(9)
                ->withQueryString(),
            'galleries' => Gallery::query()->active()->with('albumCategory')->ordered()->take(6)->get(),
        ]);
    }

    /**
     * Detail kegiatan.
     */
    public function show(string $slug): View
    {
        $activity = Activity::query()
            ->published()
            ->where('slug', $slug)
            ->with(['region', 'program', 'event', 'gallery.items', 'tags'])
            ->firstOrFail();

        $activity->increment('views');

        return view('pages.activity-show', [
            'profile' => OrganizationProfile::current(),
            'activity' => $activity,
            'related' => Activity::query()
                ->published()
                ->whereKeyNot($activity->getKey())
                ->ordered()
                ->take(3)
                ->get(),
            'regions' => Region::query()->active()->ordered()->take(8)->get(),
        ]);
    }

    /**
     * Detail agenda/acara.
     */
    public function showEvent(string $slug): View
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->with(['region', 'program', 'activities' => fn ($q) => $q->published()->ordered()])
            ->firstOrFail();

        $event->increment('views');

        return view('pages.event-show', [
            'profile' => OrganizationProfile::current(),
            'event' => $event,
            'agendas' => Event::query()
                ->upcoming()
                ->whereKeyNot($event->getKey())
                ->take(3)
                ->get(),
        ]);
    }
}
