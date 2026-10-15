@props(['video', 'featured' => false])

@php
    $url = route('video.show', $video->slug);
    $isi = ringkas_isi($video->description, null, $featured ? 260 : 190);
    $durasi = method_exists($video, 'durationLabel') ? $video->durationLabel() : null;
@endphp

@if ($featured)
    <article class="card group grid overflow-hidden lg:grid-cols-[1.05fr_1fr]">
        <a href="{{ $url }}" class="relative block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-content-media :image="$video->thumbnailUrl()" :alt="$video->title" ratio="wide" class="h-full lg:aspect-auto" />
            <span class="absolute inset-0 flex items-center justify-center">
                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-gold/95 text-navy-deep transition group-hover:scale-105">
                    <svg class="ml-1 h-7 w-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </span>
            </span>
        </a>

        <div class="flex flex-col p-6 md:p-8">
            <p class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="card-kicker">{{ $video->categoryLabel() }}</span>
                <span class="meta">{{ tanggal_id($video->published_at, 'd F Y') }}@if ($durasi) &middot; {{ $durasi }}@endif</span>
            </p>

            <h3 class="card-title-wide mt-4 font-display text-2xl leading-snug text-navy">
                <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $video->title }}</a>
            </h3>

            <p class="card-excerpt mt-4 max-w-prose text-base leading-relaxed text-ink-soft">{{ $isi }}</p>

            <div class="mt-auto pt-7">
                <a href="{{ $url }}" class="inline-flex items-center gap-2 text-base font-semibold text-terracotta">
                    Tonton video <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </article>
@else
    <article class="card group flex h-full flex-col">
        <a href="{{ $url }}" class="relative block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-content-media :image="$video->thumbnailUrl()" :alt="$video->title" />

            <span class="absolute inset-0 flex items-center justify-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-gold/95 text-navy-deep transition group-hover:scale-105">
                    <svg class="ml-1 h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </span>
            </span>
        </a>

        <div class="flex flex-1 flex-col p-6 md:p-8">
            <p class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="card-kicker">{{ $video->categoryLabel() }}</span>
                <span class="meta">{{ tanggal_id($video->published_at, 'd F Y') }}</span>
            </p>

            <h3 class="card-title mt-4 font-display text-xl leading-snug text-navy">
                <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $video->title }}</a>
            </h3>

            <p class="card-excerpt mt-4 text-base leading-relaxed text-ink-soft">{{ $isi }}</p>
        </div>
    </article>
@endif
