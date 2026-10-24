@php
    /*
     * Metadata <head> memakai tiga lapis nilai:
     *   1. catatan SEO konten (tabel seo_metadata, diisi lewat panel),
     *   2. @section pada view halaman,
     *   3. nilai bawaan profil organisasi.
     * Semua nilai di-escape oleh Blade; deskripsi dan kata kunci dibersihkan
     * dari tag agar tidak pernah menjadi HTML mentah di <head>.
     */
    $profile = $profile ?? \App\Models\OrganizationProfile::current();
    $seo = $seo ?? null;

    $bagian = static fn (string $kunci): string => trim((string) \Illuminate\Support\Facades\View::yieldContent($kunci));

    $bersihkan = static fn (?string $nilai): string => trim(
        preg_replace('/\s+/u', ' ', strip_tags((string) $nilai)) ?? ''
    );

    $judulBagian = $bagian('title');
    $judulMeta = $bersihkan($seo?->meta_title);

    if ($judulMeta !== '') {
        $judulDokumen = $judulMeta;
    } elseif ($judulBagian !== '') {
        $judulDokumen = $judulBagian.' — '.$profile->abbreviation;
    } else {
        $judulDokumen = $profile->name;
    }

    $deskripsi = $bersihkan(
        $seo?->meta_description
        ?: $bagian('meta_description')
        ?: $profile->short_description
    );

    $deskripsiOg = $bersihkan($bagian('og_description')) ?: $deskripsi;

    $kataKunci = $bersihkan(
        $seo?->meta_keywords
        ?: $bagian('meta_keywords')
        ?: 'PERWAMA INA, perempuan Maluku, organisasi perempuan, pemberdayaan perempuan'
    );

    $gambarOg = $seo?->ogImageUrl() ?: media_url($bagian('og_image') ?: null);

    $kanonik = $seo?->canonical_url ?: url()->current();
    $jenisOg = $seoType ?? 'website';
    $judulOg = $judulMeta !== '' ? $judulMeta : ($bersihkan($bagian('og_title')) ?: $profile->name);
@endphp

<title>{{ $judulDokumen }}</title>
@if ($deskripsi !== '')
    <meta name="description" content="{{ $deskripsi }}">
@endif
@if ($kataKunci !== '')
    <meta name="keywords" content="{{ $kataKunci }}">
@endif
<meta name="theme-color" content="#1F2A44">
<link rel="canonical" href="{{ $kanonik }}">

<meta property="og:type" content="{{ $jenisOg }}">
<meta property="og:site_name" content="{{ $profile->name }}">
<meta property="og:title" content="{{ $judulOg }}">
@if ($deskripsiOg !== '')
    <meta property="og:description" content="{{ $deskripsiOg }}">
@endif
<meta property="og:url" content="{{ $kanonik }}">
@if ($gambarOg)
    <meta property="og:image" content="{{ $gambarOg }}">
@endif

<meta name="twitter:card" content="{{ $gambarOg ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $judulOg }}">
@if ($deskripsiOg !== '')
    <meta name="twitter:description" content="{{ $deskripsiOg }}">
@endif
@if ($gambarOg)
    <meta name="twitter:image" content="{{ $gambarOg }}">
@endif
