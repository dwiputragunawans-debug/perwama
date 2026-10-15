@php
    /*
     * Pengaturan tampilan panel (Pengaturan -> Tampilan Panel). Nilai dibaca
     * dari basis data sehingga background login, logo, dan branding dapat
     * diubah tanpa mengubah kode.
     */
    $panel = \App\Models\PanelSetting::current();
    $loginBackground = media_url($panel->login_background);
    $loginColor = $panel->login_background_color ?: '#152038';
    $logoHeight = $panel->brand_logo_height ?: '2.5rem';
@endphp

<style>
    /*
     * Sidebar admin berlatar putih penuh. Filament secara bawaan membuat latar
     * sidebar transparan pada layar besar (lg), sehingga warna halaman ikut
     * terlihat. Selektor `.fi-body .fi-sidebar` sengaja lebih spesifik agar
     * menimpa aturan bawaan tersebut.
     */
    .fi-body .fi-sidebar {
        background-color: #ffffff;
    }

    /* Background halaman login (dan halaman sederhana lain pada panel). */
    .fi-simple-layout {
        @if ($loginBackground)
            background-image: url('{{ $loginBackground }}');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        @endif
        background-color: {{ $loginColor }};
    }

    /*
     * Logo diperbesar: paling besar pada halaman login, lalu di dalam panel
     * (topbar & sidebar). Tinggi dasar tetap mengikuti pengaturan "Tinggi logo",
     * hanya dinaikkan skalanya. `!important` dipakai karena Filament menulis
     * tinggi logo secara inline.
     */
    .fi-logo {
        width: auto;
    }

    .fi-simple-header .fi-logo {
        height: calc({{ $logoHeight }} * 1.8) !important;
    }

    .fi-sidebar-header-logo-ctn .fi-logo,
    .fi-topbar-start .fi-logo {
        height: calc({{ $logoHeight }} * 1.3) !important;
    }

    /*
     * Teks nama & slogan di samping logo (lihat resources/views/filament/brand-text).
     */
    .panel-brand-text {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
        margin-inline-start: 0.75rem;
        line-height: 1.2;
    }

    .panel-brand-name {
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: var(--gray-950);
    }

    .panel-brand-tagline {
        margin-block-start: 0.15rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #8a6a1e;
    }

    .dark .panel-brand-name {
        color: var(--color-white);
    }

    .dark .panel-brand-tagline {
        color: var(--gold-500, #d9b44a);
    }

    /*
     * Bila logo belum diunggah, Filament menampilkan nama brand sebagai teks
     * (div.fi-logo) di topbar/sidebar. Sembunyikan agar tidak berulang dengan
     * teks di samping logo.
     */
    .fi-topbar-start div.fi-logo,
    .fi-sidebar-header-logo-ctn div.fi-logo {
        display: none;
    }

    /*
     * Tombol aksi utama memakai warna brand #1F2A44 dengan teks putih.
     * Filament v5 secara bawaan memakai shade terang (primary-400) dengan teks
     * gelap pada mode terang sehingga kontrasnya kurang jelas. Tombol bertipe
     * outlined (mis. "Batal") tetap dibiarkan netral.
     */
    .fi-btn.fi-color-primary:not(.fi-outlined) {
        --bg: #1F2A44;
        --hover-bg: #2C3A5C;
        --text: #ffffff;
        --hover-text: #ffffff;
    }

    /*
     * Ukuran huruf seluruh panel dinaikkan satu tingkat dari skala bawaan
     * Filament supaya nyaman dibaca pengelola yang usianya sudah berumur.
     * Setiap tingkat memakai ukuran milik tingkat di atasnya:
     *
     *   text-xs   12px -> 14px        text-xl   20px -> 24px
     *   text-sm   14px -> 16px        text-2xl  24px -> 30px
     *   text-base 16px -> 18px        text-3xl  30px -> 36px
     *   text-lg   18px -> 20px
     *
     * Seluruh gaya bawaan panel (tombol, label, tabel, badge, pesan, isi
     * artikel/prose, dsb.) memakai variabel ini, misalnya
     * `font-size: var(--text-sm)`, sehingga satu penimpaan cukup untuk semua.
     *
     * Ukuran akar (html) sengaja TIDAK diubah agar jarak, lebar sidebar, dan
     * tinggi baris tabel tetap seperti semula — hanya hurufnya yang membesar.
     */
    :root {
        --text-xs: 0.875rem;
        --text-xs--line-height: calc(1.25 / 0.875);
        --text-sm: 1rem;
        --text-sm--line-height: calc(1.5 / 1);
        --text-base: 1.125rem;
        --text-base--line-height: calc(1.75 / 1.125);
        --text-lg: 1.25rem;
        --text-lg--line-height: calc(1.75 / 1.25);
        --text-xl: 1.5rem;
        --text-xl--line-height: calc(2 / 1.5);
        --text-2xl: 1.875rem;
        --text-2xl--line-height: calc(2.25 / 1.875);
        --text-3xl: 2.25rem;
        --text-3xl--line-height: calc(2.5 / 2.25);
    }

    /*
     * Teks yang tidak memakai ukuran sendiri (mengikuti ukuran dasar) ikut
     * naik dari 16px menjadi 18px, mis. paragraf pada konten yang diwarisi.
     */
    body {
        font-size: var(--text-base);
    }

    /*
     * Avatar inisial pada dasbor panel (daftar "Pendaftaran Anggota Terbaru").
     *
     * Warna bulatan bergilir mengikuti warna brand panel — navy, terracotta,
     * hijau, lalu emas tua — supaya tiap baris mudah dibedakan tanpa perlu
     * menyimpan warna per anggota (lihat App\Filament\Widgets\LatestMembersTable).
     */
    .dashboard-member {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }

    .dashboard-member-avatar {
        display: inline-flex;
        height: 2.25rem;
        width: 2.25rem;
        flex: none;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background-color: #1F2A44;
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #ffffff;
    }

    .fi-ta-row:nth-child(4n + 1) .dashboard-member-avatar {
        background-color: #1F2A44;
    }

    .fi-ta-row:nth-child(4n + 2) .dashboard-member-avatar {
        background-color: #B85C3A;
    }

    .fi-ta-row:nth-child(4n + 3) .dashboard-member-avatar {
        background-color: #3F6B45;
    }

    .fi-ta-row:nth-child(4n + 4) .dashboard-member-avatar {
        background-color: #8A6A1E;
    }

    .dashboard-member-name {
        font-weight: 600;
        color: var(--gray-950);
    }

    .dark .dashboard-member-name {
        color: var(--color-white);
    }

    /*
     * Keterangan dari pustaka pihak ketiga memiliki ukuran tetap sehingga
     * tidak ikut skala di atas: tooltip (tippy) dan angka kecil pada filter
     * rentang (noUiSlider) ikut dinaikkan satu tingkat.
     */
    .tippy-box {
        font-size: 1rem;
    }

    .noUi-value-sub {
        font-size: 0.75rem;
    }
</style>

