<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $profile = $profile ?? \App\Models\OrganizationProfile::current();
        // Favicon memakai pengaturan yang sama dengan logo panel (Pengaturan → Tampilan Panel).
        $favicon = media_url(\App\Models\PanelSetting::current()->favicon);
    @endphp

    @include('partials.seo', ['profile' => $profile, 'seo' => $seo ?? null, 'seoType' => $seoType ?? 'website'])

    <meta name="session-refresh-url" content="{{ route('session.refresh') }}">
    {{-- Tujuan laporan lama kunjungan halaman (statistik dasbor panel). --}}
    <meta name="visit-duration-url" content="{{ route('visit.duration') }}">

    @if ($favicon)
        <link rel="icon" href="{{ $favicon }}">
    @endif

    @if (request()->routeIs('home'))
        @php
            $skemaOrganisasi = array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $profile->name,
                'alternateName' => $profile->abbreviation,
                'url' => route('home'),
                'logo' => media_url($profile->logo),
                'description' => trim(strip_tags((string) $profile->short_description)),
                'foundingDate' => $profile->founded_year,
                'email' => $profile->email,
                'telephone' => $profile->phone,
                'address' => $profile->address ? array_filter([
                    '@type' => 'PostalAddress',
                    'streetAddress' => $profile->address,
                    'addressLocality' => $profile->city,
                    'addressRegion' => $profile->province,
                    'postalCode' => $profile->postal_code,
                    'addressCountry' => 'ID',
                ]) : null,
            ]);
        @endphp
        {{-- Data terstruktur organisasi: dibaca mesin pencari pada halaman beranda. --}}
        <script type="application/ld+json">{!! json_encode($skemaOrganisasi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-cream text-ink antialiased">
    <a href="#konten-utama" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[200] focus:rounded-full focus:bg-gold focus:px-5 focus:py-2 focus:font-semibold focus:text-navy-deep">
        Lewati ke konten utama
    </a>

    @include('partials.header', ['profile' => $profile])

    <main id="konten-utama" class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer', ['profile' => $profile])

    <button type="button"
            data-back-to-top
            aria-label="Kembali ke atas"
            class="fixed bottom-6 right-6 z-50 flex h-11 w-11 items-center justify-center rounded-full bg-navy text-white opacity-0 shadow-lg transition-opacity duration-300 hover:bg-navy-deep">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
        </svg>
    </button>

    @stack('scripts')
</body>
</html>
