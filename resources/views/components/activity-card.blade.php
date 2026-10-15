@props(['activity', 'featured' => false])

@php
    $url = route('activity.show', $activity->slug);
    $isi = ringkas_isi($activity->excerpt, $activity->description, $featured ? 260 : 200);
    $lokasi = $activity->location ?: ($activity->region?->name ?: '');
@endphp

@if ($featured)
    <article class="card group grid overflow-hidden lg:grid-cols-[1.05fr_1fr]">
        <a href="{{ $url }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-content-media :image="media_url($activity->cover_image)" :alt="$activity->title" ratio="wide" class="h-full lg:aspect-auto" />
        </a>

        <div class="flex flex-col p-6 md:p-8">
            <p class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="card-kicker">{{ $activity->categoryLabel() }}</span>
                <time class="meta" datetime="{{ optional($activity->activity_date)->toDateString() }}">{{ tanggal_id($activity->activity_date, 'd F Y') }}</time>
            </p>

            <h3 class="card-title-wide mt-4 font-display text-2xl leading-snug text-navy">
                <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $activity->title }}</a>
            </h3>

            <p class="card-excerpt mt-4 max-w-prose text-base leading-relaxed text-ink-soft">{{ $isi }}</p>

            <div class="mt-auto flex flex-wrap items-center justify-between gap-4 pt-7">
                <span class="meta">
                    {{ $lokasi }}
                    @if ($activity->participant_count)
                        &middot; {{ number_format((int) $activity->participant_count, 0, ',', '.') }} peserta
                    @endif
                </span>
                <a href="{{ $url }}" class="inline-flex items-center gap-2 text-base font-semibold text-terracotta">
                    Lihat dokumentasi <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </article>
@else
    <article class="card group flex h-full flex-col">
        <a href="{{ $url }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-content-media :image="media_url($activity->cover_image)" :alt="$activity->title" />
        </a>

        <div class="flex flex-1 flex-col p-6 md:p-8">
            <p class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="card-kicker">{{ $activity->categoryLabel() }}</span>
                <time class="meta" datetime="{{ optional($activity->activity_date)->toDateString() }}">{{ tanggal_id($activity->activity_date, 'd F Y') }}</time>
            </p>

            <h3 class="card-title mt-4 font-display text-xl leading-snug text-navy">
                <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $activity->title }}</a>
            </h3>

            <p class="card-excerpt mt-4 text-base leading-relaxed text-ink-soft">{{ $isi }}</p>

            <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-7">
                <span class="meta">{{ $lokasi }}</span>
                @if ($activity->participant_count)
                    <span class="meta">{{ number_format((int) $activity->participant_count, 0, ',', '.') }} peserta</span>
                @endif
            </div>
        </div>
    </article>
@endif
