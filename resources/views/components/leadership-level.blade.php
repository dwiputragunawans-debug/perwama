@props([
    'people',
    'label',
])

@php
    /*
     * Satu tingkat kepengurusan (DPP, DPW, atau PC).
     *
     * Urutan kartu sepenuhnya mengikuti kolom Urutan di panel admin
     * (Manajemen → Pengurus). Kartu dengan Urutan terkecil tampil lebih besar
     * sebagai pembuka tingkat, sedangkan sisanya tampil sebagai kartu sejajar
     * dengan urutan yang sama. Warna dan tipografi memakai tema situs: navy,
     * terracotta, hijau, gold, dan Poppins. Bulatan inisial dipakai bila foto
     * pengurus belum diunggah.
     */
    $leader = $people->first();

    $lainnya = $people->skip(1);

    // Warna bulatan inisial bergilir memakai palet situs.
    $warnaAvatar = ['bg-navy text-white', 'bg-terracotta text-white', 'bg-green text-white', 'bg-gold text-navy-deep'];
@endphp

<h3 class="text-center font-display text-xl font-semibold text-navy sm:text-2xl">{{ $label }}</h3>

@if ($leader)
    <article class="card mt-8 grid items-center gap-6 p-8 md:grid-cols-[auto_1fr]">
        <div class="mx-auto md:mx-0">
            @if ($leader->photo)
                <img src="{{ media_url($leader->photo) }}" alt="{{ $leader->name }}"
                     class="h-28 w-28 rounded-full object-cover" loading="lazy">
            @else
                <span class="flex h-28 w-28 items-center justify-center rounded-full bg-navy font-display text-3xl font-semibold text-white">
                    {{ Str::substr($leader->name, 0, 1) }}
                </span>
            @endif
        </div>

        <div class="text-center md:text-left">
            <p class="section-eyebrow">{{ $leader->position }}</p>
            <h4 class="mt-2 font-display text-2xl font-semibold text-navy">{{ $leader->name }}</h4>

            @if ($leader->period)
                <p class="meta mt-1">Periode {{ $leader->period }}</p>
            @endif
            @if ($leader->region)
                <p class="meta">{{ $leader->region->name }}</p>
            @endif
            @if ($leader->bio)
                <p class="mt-4 text-base italic leading-relaxed text-ink-soft">“{{ $leader->bio }}”</p>
            @endif
        </div>
    </article>
@endif

@if ($lainnya->isNotEmpty())
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($lainnya as $orang)
            <article class="card p-6 text-center" data-reveal>
                @if ($orang->photo)
                    <img src="{{ media_url($orang->photo) }}" alt="{{ $orang->name }}"
                         class="mx-auto h-24 w-24 rounded-full object-cover" loading="lazy">
                @else
                    <span class="mx-auto flex h-24 w-24 items-center justify-center rounded-full {{ $warnaAvatar[$loop->index % 4] }} font-display text-3xl font-semibold">
                        {{ Str::substr($orang->name, 0, 1) }}
                    </span>
                @endif

                <h4 class="mt-4 font-display text-lg font-semibold text-navy">{{ $orang->name }}</h4>
                <p class="mt-1 text-sm font-semibold tracking-[0.01em] text-terracotta">{{ $orang->position }}</p>

                @if ($orang->period)
                    <p class="mt-2 meta">Periode {{ $orang->period }}</p>
                @endif
                @if ($orang->region)
                    <p class="meta">{{ $orang->region->name }}</p>
                @endif
                @if ($orang->bio)
                    <p class="mt-3 text-base leading-relaxed text-ink-soft">{{ ringkas_isi($orang->bio, null, 150) }}</p>
                @endif
            </article>
        @endforeach
    </div>
@endif
