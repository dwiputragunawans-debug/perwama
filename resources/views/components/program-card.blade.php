@props(['program', 'featured' => false])

@php
    $url = route('program.show', $program->slug);
    $isi = ringkas_isi($program->excerpt, $program->description, $featured ? 260 : 200);
@endphp

@if ($featured)
    {{-- Kartu unggulan: susunan mendatar agar daftar tidak terasa seragam. --}}
    <article class="card group grid overflow-hidden lg:grid-cols-[1.05fr_1fr]">
        <a href="{{ $url }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-content-media :image="media_url($program->cover_image)" :alt="$program->title" ratio="wide" class="h-full lg:aspect-auto" />
        </a>

        <div class="flex flex-col p-6 md:p-8">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                @if ($program->category)
                    <span class="card-kicker">{{ $program->category->name }}</span>
                @endif
                <span class="meta">{{ $program->statusLabel() }}</span>
            </div>

            <h3 class="card-title-wide mt-4 font-display text-2xl leading-snug text-navy">
                <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $program->title }}</a>
            </h3>

            <p class="card-excerpt mt-4 max-w-prose text-base leading-relaxed text-ink-soft">{{ $isi }}</p>

            <div class="mt-auto flex flex-wrap items-center justify-between gap-4 pt-7">
                <span class="meta">{{ $program->region?->name ?: 'Program nasional' }}</span>
                <a href="{{ $url }}" class="inline-flex items-center gap-2 text-base font-semibold text-terracotta">
                    Baca selengkapnya <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </article>
@else
    <article class="card group flex h-full flex-col">
        <a href="{{ $url }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
            <x-content-media :image="media_url($program->cover_image)" :alt="$program->title" />
        </a>

        <div class="flex flex-1 flex-col p-6 md:p-8">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                @if ($program->category)
                    <span class="card-kicker">{{ $program->category->name }}</span>
                @endif
                <span class="meta">{{ $program->statusLabel() }}</span>
            </div>

            <h3 class="card-title mt-4 font-display text-xl leading-snug text-navy">
                <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $program->title }}</a>
            </h3>

            <p class="card-excerpt mt-4 text-base leading-relaxed text-ink-soft">{{ $isi }}</p>

            @if ($program->region?->name)
                <p class="meta mt-5">{{ $program->region->name }}</p>
            @endif

            <div class="mt-auto pt-7">
                <a href="{{ $url }}" class="inline-flex items-center gap-2 text-base font-semibold text-terracotta">
                    Selengkapnya <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </article>
@endif
