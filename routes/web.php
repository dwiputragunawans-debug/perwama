<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\VisitController;
use App\Models\Activity;
use App\Models\News;
use App\Models\OrganizationProfile;
use App\Models\Program;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Situs Publik PERWAMA INA
|--------------------------------------------------------------------------
| Struktur mengikuti arsitektur informasi PRD:
| Home · Tentang · Program · Wilayah · Kegiatan · Cerita ·
| Berita · Galeri · Bergabung · Kontak
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/tentang', AboutController::class)->name('about');

Route::get('/program', [ProgramController::class, 'index'])->name('program.index');
Route::get('/program/{slug}', [ProgramController::class, 'show'])->name('program.show');

Route::get('/wilayah', [RegionController::class, 'index'])->name('region.index');
Route::get('/wilayah/{slug}', [RegionController::class, 'show'])->name('region.show');

Route::get('/kegiatan', [ActivityController::class, 'index'])->name('activity.index');
Route::get('/kegiatan/agenda/{slug}', [ActivityController::class, 'showEvent'])->name('event.show');
Route::get('/kegiatan/{slug}', [ActivityController::class, 'show'])->name('activity.show');

Route::get('/cerita', [StoryController::class, 'index'])->name('story.index');
Route::get('/cerita/{slug}', [StoryController::class, 'show'])->name('story.show');

Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/{slug}', [NewsController::class, 'show'])->name('news.show');

Route::get('/publikasi', [PublicationController::class, 'index'])->name('publication.index');
Route::get('/publikasi/{slug}', [PublicationController::class, 'show'])->name('publication.show');

Route::get('/galeri', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/galeri/video', [GalleryController::class, 'videos'])->name('video.index');
Route::get('/galeri/video/{slug}', [GalleryController::class, 'showVideo'])->name('video.show');
Route::get('/galeri/{slug}', [GalleryController::class, 'show'])->name('gallery.show');

Route::get('/bergabung', [JoinController::class, 'index'])->name('join');
Route::post('/bergabung', [JoinController::class, 'storeMembership'])->name('join.store');
Route::post('/bergabung/mitra', [JoinController::class, 'storePartnership'])->name('join.partnership');

Route::get('/kontak', [ContactController::class, 'index'])->name('contact');
Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');

// Pencarian global sederhana.
Route::get('/cari', function (Request $request) {
    $term = trim((string) $request->query('q'));

    return view('pages.search', [
        'profile' => OrganizationProfile::current(),
        'term' => $term,
        'programs' => $term === '' ? collect() : Program::query()->published()->where('title', 'like', "%{$term}%")->ordered()->take(6)->get(),
        'newsItems' => $term === '' ? collect() : News::query()->published()->where('title', 'like', "%{$term}%")->ordered()->take(6)->get(),
        'stories' => $term === '' ? collect() : Story::query()->published()->where('title', 'like', "%{$term}%")->ordered()->take(6)->get(),
        'activities' => $term === '' ? collect() : Activity::query()->published()->where('title', 'like', "%{$term}%")->ordered()->take(6)->get(),
    ]);
})->name('search');

/*
|--------------------------------------------------------------------------
| Penyegar Sesi (dipakai formulir publik)
|--------------------------------------------------------------------------
| Formulir yang lama dibiarkan terbuka dapat kehilangan sesi sehingga
| pengiriman gagal dengan "419 Page Expired". Endpoint ini menjaga sesi tetap
| hidup sekaligus mengembalikan token CSRF terbaru agar formulir selalu dapat
| dikirim (lihat resources/js/app.js).
*/
Route::get('/sesi/segar', function () {
    return response()->json(['token' => csrf_token()]);
})->name('session.refresh');

/*
|--------------------------------------------------------------------------
| Laporan Lama Kunjungan Halaman
|--------------------------------------------------------------------------
| Skrip situs publik mengirim berapa lama pengunjung berada di sebuah halaman
| saat halaman ditinggalkan. Datanya melengkapi catatan kunjungan yang dibuat
| middleware RecordPageView dan dipakai oleh dasbor panel
| ("Rata-rata Waktu Akses"), lihat resources/js/app.js.
*/
Route::post('/jejak-kunjungan', [VisitController::class, 'storeDuration'])->name('visit.duration');
