<?php

namespace App\Http\Controllers;

use App\Models\MembershipApplication;
use App\Models\OrganizationProfile;
use App\Models\PartnershipRequest;
use App\Models\Region;
use App\Models\Statistic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JoinController extends Controller
{
    /**
     * Halaman "Bergabung" — syarat, alur, dan formulir pendaftaran.
     */
    public function index(): View
    {
        return view('pages.join', [
            'profile' => OrganizationProfile::current(),
            'provinces' => Region::query()->active()->provinces()->ordered()->get(),
            'statistics' => Statistic::query()->active()->ordered()->get(),
            'form' => session('form', 'anggota'),
        ]);
    }

    /**
     * Simpan pendaftaran keanggotaan / relawan / mitra.
     */
    public function storeMembership(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:32'],
            'birth_place' => ['nullable', 'string', 'max:120'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:Perempuan,Laki-laki'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'whatsapp' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'education' => ['nullable', 'string', 'max:120'],
            'occupation' => ['nullable', 'string', 'max:120'],
            'interest_field' => ['nullable', 'string', 'max:120'],
            'maluku_origin' => ['nullable', 'string', 'max:120'],
            'domicile_region' => ['nullable', 'string', 'max:120'],
            'membership_type' => ['required', 'in:anggota,relawan,mitra,kolaborasi'],
            'motivation' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'full_name' => 'nama lengkap',
            'whatsapp' => 'nomor WhatsApp',
            'membership_type' => 'jenis keikutsertaan',
        ]);

        $data['status'] = 'pending';

        MembershipApplication::query()->create($data);

        return redirect()
            ->route('join')
            ->with('success', 'Terima kasih! Pendaftaran Anda sudah kami terima dan akan ditinjau oleh tim PERWAMA INA.')
            ->with('form', $data['membership_type']);
    }

    /**
     * Simpan permohonan kerja sama / kolaborasi.
     */
    public function storePartnership(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'type' => ['required', 'in:anggota,relawan,mitra,kolaborasi'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $data['status'] = 'pending';

        PartnershipRequest::query()->create($data);

        return redirect()
            ->route('join')
            ->with('success', 'Permohonan kerja sama Anda telah kami terima. Tim kami akan menghubungi Anda segera.')
            ->with('form', 'mitra');
    }
}
