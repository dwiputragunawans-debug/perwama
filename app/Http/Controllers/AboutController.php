<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Management;
use App\Models\OrganizationHistory;
use App\Models\OrganizationProfile;
use App\Models\ProgramCategory;
use App\Models\Statistic;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    /**
     * Halaman "Tentang" — profil, visi & misi, bidang pengabdian, sejarah, dan
     * struktur kepengurusan.
     */
    public function __invoke(): View
    {
        $profile = OrganizationProfile::current();

        return view('pages.about', [
            'profile' => $profile,
            'histories' => OrganizationHistory::query()->active()->ordered()->get(),
            'management' => Management::query()->active()->ordered()->get()->groupBy('level'),
            'statistics' => Statistic::query()->active()->ordered()->get(),
            /*
             * Bidang pengabdian mengikuti kategori program, lengkap dengan
             * jumlah program tiap bidang untuk keterangan pada kartu.
             */
            'focusAreas' => ProgramCategory::query()->active()->ordered()->withCount('programs')->get(),
            /*
             * Foto hero memakai unggahan khusus (Tentang → Profil Organisasi).
             * Bila belum ada, cover galeri pertama dipakai agar bagian hero
             * tidak kosong.
             */
            'heroCover' => Gallery::query()->active()->ordered()->value('cover_image'),
        ]);
    }
}
