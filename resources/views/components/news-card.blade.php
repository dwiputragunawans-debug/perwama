@props(['item', 'featured' => false])

@php
    $url = route('news.show', $item->slug);
    $isi = ringkas_isi($item->excerpt, $item->content, $featured ? 260 : 200);
    $tanggal = tanggal_id($item->published_at, 'd F Y');
@endphp

@if ($featured)
    <article class="card group grid overflow-hidden lg:grid-cols-[1.05fr_1fr]">
        <a href="{{ $url }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-content-media :image="media_url($item->cover_image)" :alt="$item->title" ratio="wide" class="h-full lg:aspect-auto" />
        </a>

        <div class="flex flex-col p-6 md:p-8">
            <p class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="card-kicker">{{ $item->categoryLabel() }}</span>
                <time class="meta" datetime="{{ optional($item->published_at)->toDateString() }}">{{ $tanggal }}</time>
            </p>

            <h3 class="card-title-wide mt-4 font-display text-2xl leading-snug text-navy">
                <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $item->title }}</a>
            </h3>

            <p class="card-excerpt mt-4 max-w-prose text-base leading-relaxed text-ink-soft">{{ $isi }}</p>

            <div class="mt-auto pt-7">
                <a href="{{ $url }}" class="inline-flex items-center gap-2 text-base font-semibold text-terracotta">
                    Baca berita <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </article>
@else
    <article class="card group flex h-full flex-col">
        <a href="{{ $url }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-content-media :image="media_url($item->cover_image)" :alt="$item->title" />
        </a>

        <div class="flex flex-1 flex-col p-6 md:p-8">
            <p class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="card-kicker">{{ $item->categoryLabel() }}</span>
                <time class="meta" datetime="{{ optional($item->published_at)->toDateString() }}">{{ $tanggal }}</time>
            </p>

            <h3 class="card-title mt-4 font-display text-xl leading-snug text-navy">
                <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $item->title }}</a>
            </h3>

            <p class="card-excerpt mt-4 text-base leading-relaxed text-ink-soft">{{ $isi }}</p>

            <div class="mt-auto pt-7">
                <a href="{{ $url }}" class="inline-flex items-center gap-2 text-base font-semibold text-terracotta">
                    Baca <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </article>
@endif
