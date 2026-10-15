<?php

namespace App\Http\Controllers;

use App\Models\OrganizationProfile;
use App\Models\Program;
use App\Models\ProgramCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Daftar seluruh program/kegiatan organisasi.
     */
    public function index(Request $request): View
    {
        $category = $request->query('kategori');
        $search = $request->query('q');

        $programs = Program::query()
            ->published()
            ->with(['category', 'region'])
            ->when($category, fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $category)
            ))
            ->when($search, fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
            ->ordered()
            ->paginate(9)
            ->withQueryString();

        return view('pages.program-index', [
            'profile' => OrganizationProfile::current(),
            'categories' => ProgramCategory::query()->active()->ordered()->get(),
            'programs' => $programs,
            'activeCategory' => $category,
            'search' => $search,
        ]);
    }

    /**
     * Detail program beserta capaian dan konten terkait.
     */
    public function show(string $slug): View
    {
        $program = Program::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'category',
                'region',
                'activities' => fn ($q) => $q->published()->ordered()->take(4),
                'stories' => fn ($q) => $q->published()->ordered()->take(3),
                'news' => fn ($q) => $q->published()->ordered()->take(3),
                'galleries' => fn ($q) => $q->active()->ordered()->take(4),
            ])
            ->firstOrFail();

        $program->increment('views');

        return view('pages.program-show', [
            'profile' => OrganizationProfile::current(),
            'program' => $program,
            'others' => Program::query()
                ->published()
                ->whereKeyNot($program->getKey())
                ->ordered()
                ->take(3)
                ->get(),
        ]);
    }
}
