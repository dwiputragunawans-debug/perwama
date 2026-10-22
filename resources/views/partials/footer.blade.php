@php
    /*
     * Logo footer memakai sumber dan bentuk yang sama dengan header website
     * (Pengaturan → Tampilan Panel), dengan logo profil organisasi sebagai
     * cadangan bila logo panel belum diunggah.
     */
    $panelBrand = \App\Models\PanelSetting::current();
    $footerLogo = media_url($panelBrand->brand_logo ?: $profile->logo);
@endphp

<footer class="bg-navy-deep text-white/75">
    <div class="container-page grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-4">
        <div class="lg:col-span-1">
            <div class="flex items-center gap-3">
                @if ($footerLogo)
                    <img src="{{ $footerLogo }}" alt="{{ $panelBrand->displayName() }}" class="h-11 w-11 rounded-full bg-white object-contain p-0.5">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-gold font-display text-xl font-bold text-navy-deep">P</span>
                @endif
                <span class="font-display text-xl font-semibold text-white">{{ $profile->abbreviation ?: 'PERWAMA INA' }}</span>
            </div>

            <p class="mt-5 text-base leading-relaxed">{{ $profile->short_description ?: $profile->tagline }}</p>

            <div class="mt-6 flex flex-wrap gap-2">
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'twitter' => 'X', 'tiktok' => 'TikTok'] as $key => $label)
                    @if ($profile->{$key})
                        <a href="{{ $profile->{$key} }}" target="_blank" rel="noopener"
                           class="rounded-full border border-white/20 px-3.5 py-1.5 text-sm font-medium transition hover:border-gold hover:text-gold">
                            {{ $label }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="font-display text-lg font-semibold text-white">Jelajahi</h3>
            <ul class="mt-5 space-y-3 text-sm">
                <li><a href="{{ route('about') }}" class="transition hover:text-gold">Tentang Kami</a></li>
                <li><a href="{{ route('program.index') }}" class="transition hover:text-gold">Program</a></li>
                <li><a href="{{ route('region.index') }}" class="transition hover:text-gold">Wilayah</a></li>
                <li><a href="{{ route('activity.index') }}" class="transition hover:text-gold">Kegiatan</a></li>
                <li><a href="{{ route('story.index') }}" class="transition hover:text-gold">Cerita Perempuan</a></li>
            </ul>
        </div>

        <div>
            <h3 class="font-display text-lg font-semibold text-white">Informasi</h3>
            <ul class="mt-5 space-y-3 text-sm">
                <li><a href="{{ route('news.index') }}" class="transition hover:text-gold">Berita</a></li>
                <li><a href="{{ route('publication.index') }}" class="transition hover:text-gold">Publikasi</a></li>
                <li><a href="{{ route('gallery.index') }}" class="transition hover:text-gold">Galeri</a></li>
                <li><a href="{{ route('video.index') }}" class="transition hover:text-gold">Video</a></li>
                <li><a href="{{ route('join') }}" class="transition hover:text-gold">Bergabung</a></li>
            </ul>
        </div>

        <div>
            <h3 class="font-display text-lg font-semibold text-white">Hubungi Kami</h3>
            <ul class="mt-5 space-y-3 text-sm">
                @if ($profile->address)
                    <li class="leading-relaxed">{{ $profile->address }}@if ($profile->city), {{ $profile->city }}@endif @if ($profile->postal_code) {{ $profile->postal_code }}@endif</li>
                @endif
                @if ($profile->phone)
                    <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $profile->phone) }}" class="transition hover:text-gold">{{ $profile->phone }}</a></li>
                @endif
                @if ($profile->email)
                    <li><a href="mailto:{{ $profile->email }}" class="transition hover:text-gold">{{ $profile->email }}</a></li>
                @endif
                @if (whatsapp_url($profile->whatsapp))
                    <li><a href="{{ whatsapp_url($profile->whatsapp) }}" target="_blank" rel="noopener" class="transition hover:text-gold">WhatsApp: {{ $profile->whatsapp }}</a></li>
                @endif
                <li><a href="{{ route('contact') }}" class="inline-flex items-center gap-1.5 font-semibold text-gold transition hover:text-gold-warm">Kirim pesan →</a></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-page flex flex-col items-center justify-between gap-3 py-6 text-sm sm:flex-row">
            <p>&copy; {{ now()->year }} {{ $profile->name }}. Seluruh Hak Cipta Dilindungi.</p>
            <!-- <p>Didirikan tahun {{ $profile->founded_year ?: '—' }} · {{ $profile->city ?: 'Ambon' }}, Indonesia</p> -->
        </div>
    </div>
</footer>
