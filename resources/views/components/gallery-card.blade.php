@props(['gallery', 'wide' => false])

@php
    $jumlah = ($gallery->items_count ?? $gallery->items->count());
@endphp

<article class="card group relative overflow-hidden">
    <a href="{{ route('gallery.show', $gallery->slug) }}" class="block">
        <x-content-media :image="media_url($gallery->cover_image)" :alt="$gallery->title" :ratio="$wide ? 'wide' : 'grid'" />

        <div class="absolute inset-0 bg-gradient-to-t from-navy-deep/92 via-navy-deep/30 to-transparent"></div>

        <div class="absolute inset-x-0 bottom-0 p-6 md:p-7">
            <p class="card-kicker text-gold">{{ $gallery->albumLabel() }}</p>
            <h3 class="mt-2 font-display text-xl leading-snug text-white">{{ $gallery->title }}</h3>
            <p class="meta mt-2 text-white/80">
                {{ $jumlah }} media
                @if ($gallery->event_date)
                    &middot; {{ tanggal_id($gallery->event_date, 'd F Y') }}
                @endif
            </p>
        </div>
    </a>
</article>
