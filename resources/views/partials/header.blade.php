@php
    /*
     * Menu navigasi dibaca dari basis data (Pengaturan → Menu Website) sehingga
     * pengelola dapat memunculkan atau menyembunyikan tiap menu tanpa mengubah
     * kode. Bila tabel menu belum diisi, menu bawaan tetap dipakai agar situs
     * tidak pernah kehilangan navigasi.
     */
    $menu = \App\Models\MenuItem::navigation();

    /*
     * Logo serta teks nama/slogan header memakai sumber yang sama dengan panel
     * admin (Pengaturan → Tampilan Panel). Bila logo panel belum diunggah,
     * logo profil organisasi dipakai sebagai cadangan.
     */
    $panelBrand = \App\Models\PanelSetting::current();
    $headerLogo = media_url($panelBrand->brand_logo ?: $profile->logo);
@endphp

<header data-site-header class="sticky top-0 z-50 bg-navy-deep/95 backdrop-blur">
    <div class="container-page flex items-center justify-between gap-4 py-3.5">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
            @if ($headerLogo)
                <img src="{{ $headerLogo }}" alt="{{ $panelBrand->displayName() }}" class="h-11 w-11 shrink-0 rounded-full bg-white object-contain p-0.5">
            @else
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gold font-display text-xl font-bold text-navy-deep">P</span>
            @endif
            {{--
                Identitas organisasi tampil dua baris pada semua ukuran layar:
                nama singkat (tebal, putih) di atas dan nama lengkap (emas) di
                bawahnya — bentuk yang sama dengan tampilan desktop. Baris nama
                lengkap dulu disembunyikan di bawah 640px (`hidden sm:block`)
                sehingga di ponsel hanya singkatan yang terlihat. Di ponsel
                baris itu memakai 11px karena langkah terkecil tema ini
                (`text-xs` = 14px) terlalu lebar untuk muat satu baris di
                samping logo; mulai 640px ukurannya kembali `text-xs` seperti
                desktop. `min-w-0` pada tautan dan bungkus teks membuat baris
                itu boleh menyusut dan membungkus pada layar sangat sempit
                (mis. 320px), bukan menabrak tombol menu.
            --}}
            <span class="min-w-0 leading-tight">
                <span class="block font-display text-lg font-semibold text-white">{{ $panelBrand->displayName() }}</span>
                @if (filled($panelBrand->displayTagline()))
                    <span class="block text-[11px] leading-snug tracking-[0.01em] text-gold sm:text-xs">{{ $panelBrand->displayTagline() }}</span>
                @endif
            </span>
        </a>

        <nav class="hidden items-center gap-6 xl:flex" aria-label="Navigasi utama">
            @foreach ($menu as $grup)
                @php($aktif = menu_aktif($grup['pattern']))

                @if (empty($grup['items']))
                    <a href="{{ route($grup['route']) }}"
                       class="site-nav-link {{ $aktif }}"
                       @if ($aktif === 'is-active') aria-current="page" @endif>
                        {{ $grup['label'] }}
                    </a>
                @else
                    {{-- Kelompok berisi beberapa menu tampil sebagai menu tarik-turun. --}}
                    <div class="site-nav-group" data-nav-group>
                        <button type="button"
                                data-nav-trigger
                                aria-expanded="false"
                                class="site-nav-link site-nav-trigger {{ $aktif }}">
                            {{ $grup['label'] }}
                            <svg class="site-nav-caret" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 4.5 6 8l3.5-3.5"/>
                            </svg>
                        </button>

                        <div class="site-nav-dropdown" data-nav-dropdown>
                            <ul class="site-nav-menu">
                                @foreach ($grup['items'] as $item)
                                    @php($subAktif = menu_aktif($item['pattern']))
                                    <li>
                                        <a href="{{ route($item['route']) }}"
                                           class="site-nav-dropdown-link {{ $subAktif }}"
                                           @if ($subAktif === 'is-active') aria-current="page" @endif>
                                            {{ $item['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('join') }}" class="hidden rounded-full bg-gold px-5 py-2.5 text-sm font-semibold text-navy-deep transition hover:bg-gold-warm md:inline-flex">
                Bergabung
            </a>

            <button type="button"
                    data-nav-toggle
                    aria-expanded="false"
                    aria-controls="nav-mobile"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/25 text-white xl:hidden">
                <span class="sr-only">Buka menu</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </div>
    </div>

    <div id="nav-mobile" data-nav-panel class="hidden border-t border-white/10 bg-navy-deep xl:hidden">
        <nav class="container-page grid gap-1 py-4" aria-label="Navigasi seluler">
            @foreach ($menu as $grup)
                @php($aktif = menu_aktif($grup['pattern']))

                @if (empty($grup['items']))
                    <a href="{{ route($grup['route']) }}"
                       class="rounded-lg px-3 py-3 text-sm font-medium text-white/85 transition hover:bg-white/10 hover:text-white {{ $aktif === 'is-active' ? 'bg-white/10 text-white' : '' }}">
                        {{ $grup['label'] }}
                    </a>
                @else
                    {{-- Kelompok menu sebagai judul, anggotanya diindentasi. --}}
                    <p class="px-3 pb-1 pt-3 text-xs font-semibold uppercase tracking-[0.08em] text-gold">{{ $grup['label'] }}</p>
                    @foreach ($grup['items'] as $item)
                        @php($subAktif = menu_aktif($item['pattern']))
                        <a href="{{ route($item['route']) }}"
                           class="rounded-lg py-2.5 pl-6 pr-3 text-sm font-medium text-white/85 transition hover:bg-white/10 hover:text-white {{ $subAktif === 'is-active' ? 'bg-white/10 text-white' : '' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                @endif
            @endforeach
            <a href="{{ route('join') }}" class="mt-2 rounded-full bg-gold px-5 py-3 text-center text-sm font-semibold text-navy-deep">
                Bergabung Bersama Kami
            </a>
        </nav>
    </div>

    <div class="hidden border-t border-white/10 bg-navy-deep/60 xl:block">
        <div class="container-page flex items-center justify-end gap-5 py-1.5 text-xs text-white/80">
            @if ($profile->email)
                <a href="mailto:{{ $profile->email }}" class="transition hover:text-gold">{{ $profile->email }}</a>
            @endif
            @if (whatsapp_url($profile->whatsapp))
                <a href="{{ whatsapp_url($profile->whatsapp) }}" target="_blank" rel="noopener" class="transition hover:text-gold">WhatsApp</a>
            @endif
            <form action="{{ route('search') }}" method="GET" class="flex items-center gap-1.5">
                <label for="header-search" class="sr-only">Cari</label>
                <input id="header-search" type="search" name="q" value="{{ request('q') }}" placeholder="Cari…"
                       class="w-36 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs text-white placeholder:text-white/80 focus:border-gold focus:outline-none">
            </form>
        </div>
    </div>
</header>
