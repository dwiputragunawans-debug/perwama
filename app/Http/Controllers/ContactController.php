<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\OrganizationProfile;
use App\Models\Region;
use App\Support\Honeypot;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Halaman "Kontak".
     */
    public function index(): View
    {
        return view('pages.contact', [
            'profile' => OrganizationProfile::current(),
            'provinces' => Region::query()->active()->provinces()->ordered()->get(),
        ]);
    }

    /**
     * Simpan pesan dari formulir kontak.
     */
    public function store(Request $request): RedirectResponse
    {
        /*
         * Bot yang mengisi kolom perangkap (lihat resources/views/partials/
         * honeypot.blade.php) diberi balasan sukses yang sama, tetapi pesannya
         * tidak disimpan.
         */
        if (Honeypot::triggered($request)) {
            return $this->pesanTerkirim();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ], [], [
            'name' => 'nama',
            'message' => 'pesan',
        ]);

        $data['is_read'] = false;
        $data['ip_address'] = $request->ip();

        ContactMessage::query()->create($data);

        return $this->pesanTerkirim();
    }

    private function pesanTerkirim(): RedirectResponse
    {
        return redirect()
            ->route('contact')
            ->with('success', 'Pesan Anda telah kami terima. Terima kasih telah menghubungi PERWAMA INA.');
    }
}
