@props(['story', 'featured' => false])

@php
    $url = route('story.show', $story->slug);
    $foto = media_url($story->cover_image ?: $story->person_photo);
    $isi = ringkas_isi($story->excerpt, $story->content, $featured ? 260 : 200);
    $tokoh = trim(($story->person_name ?: '').($story->person_age ? ', '.$story->person_age.' tahun' : ''));
@endphp

@if ($featured)
    <article class="card group grid overflow-hidden lg:grid-cols-[1.05fr_1fr]">
        <a href="{{ $url }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-content-media :image="$foto" :alt="$story->title" ratio="wide" class="h-full lg:aspect-auto" />
        </a>

        <div class="flex flex-col p-6 md:p-8">
            <p class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="card-kicker">{{ $story->categoryLabel() }}</span>
                @if ($story->region?->name)
                    <span class="meta">{{ $story->region->name }}</span>
                @endif
            </p>

            <h3 class="card-title-wide mt-4 font-display text-2xl leading-snug text-navy">
                <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $story->title }}</a>
            </h3>

            @if ($story->quote)
                <blockquote class="mt-5 border-l-4 border-gold pl-5 font-display text-lg italic leading-snug text-navy">
                    &ldquo;{{ $story->quote }}&rdquo;
                </blockquote>
            @endif

            <p class="card-excerpt mt-5 max-w-prose text-base leading-relaxed text-ink-soft">{{ $isi }}</p>

            <div class="mt-auto flex flex-wrap items-center justify-between gap-4 pt-7">
                <span class="meta">{{ $tokoh }}</span>
                <a href="{{ $url }}" class="inline-flex items-center gap-2 text-base font-semibold text-terracotta">
                    Baca kisahnya <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </article>
@else
    <article class="card group flex h-full flex-col">
        <a href="{{ $url }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-content-media :image="$foto" :alt="$story->title" />
        </a>

        <div class="flex flex-1 flex-col p-6 md:p-8">
            <p class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="card-kicker">{{ $story->categoryLabel() }}</span>
                @if ($story->region?->name)
                    <span class="meta">{{ $story->region->name }}</span>
                @endif
            </p>

            <h3 class="card-title mt-4 font-display text-xl leading-snug text-navy">
                <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $story->title }}</a>
            </h3>

            @if ($tokoh)
                <p class="meta mt-3">{{ $tokoh }}</p>
            @endif

            <p class="card-excerpt mt-4 text-base leading-relaxed text-ink-soft">{{ $isi }}</p>

            <div class="mt-auto pt-7">
                <a href="{{ $url }}" class="inline-flex items-center gap-2 text-base font-semibold text-terracotta">
                    Baca kisahnya <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </article>
@endif
