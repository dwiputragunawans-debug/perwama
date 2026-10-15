@php
    /*
     * Teks nama dan slogan di samping logo panel admin. Nilai dibaca dari
     * Pengaturan -> Tampilan Panel, dengan cadangan dari Profil Organisasi
     * sehingga tampilannya konsisten dengan header website.
     */
    $branding = \App\Models\PanelSetting::current();
    $brandName = $branding->displayName();
    $brandTagline = $branding->displayTagline();
@endphp

<div class="panel-brand-text">
    <span class="panel-brand-name">{{ $brandName }}</span>
    @if (filled($brandTagline))
        <span class="panel-brand-tagline">{{ $brandTagline }}</span>
    @endif
</div>
