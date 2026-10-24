@extends('layouts.app')

@section('title', $story->title)

@section('content')
    <x-page-hero
        :eyebrow="$story->categoryLabel()"
        :title="$story->title"
        :subtitle="$story->excerpt"
        :image="$story->cover_image ? media_url($story->cover_image) : null"
        :breadcrumbs="[
            'Beranda' => route('home'),
            'Cerita' => route('story.index'),
            Str::limit($story->title, 40) => route('story.show', $story->slug),
        ]" />

    <section class="section-y">
        <div class="container-page grid gap-12 lg:grid-cols-[1.6fr_1fr]">
            <article>
                @if ($story->person_name)
                    <div class="flex items-center gap-5 rounded-2xl border border-navy/10 bg-white p-6">
                        @if ($story->person_photo)
                            <img src="{{ media_url($story->person_photo) }}" alt="{{ $story->person_name }}"
                                 class="h-20 w-20 rounded-full object-cover" loading="lazy">
                        @else
                            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-navy/5 font-display text-2xl font-semibold text-navy/40">
                                {{ Str::substr($story->person_name, 0, 1) }}
                            </span>
                        @endif
                        <div>
                            <p class="font-display text-xl font-semibold text-navy">{{ $story->person_name }}</p>
                            @if ($story->person_age)
                                <p class="meta">{{ $story->person_age }} tahun</p>
                            @endif
                            @if ($story->region)
                                <p class="meta">{{ $story->region->name }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($story->quote)
                    <blockquote class="mt-10 border-l-4 border-gold bg-white px-6 py-5 font-display text-xl italic leading-relaxed text-navy">
                        “{{ $story->quote }}”
                    </blockquote>
                @endif

                @if ($story->content)
                    <div class="prose-perwama mt-10 text-base">
                        {{-- Isi dari RichEditor dibersihkan lebih dulu (lihat App\Support\HtmlSanitizer). --}}
                        {!! html_aman($story->content) !!}
                    </div>
                @endif

                @foreach ([
                    'Perjalanan' => $story->journey,
                    'Kegiatan' => $story->activities,
                    'Dampak' => $story->impact,
                ] as $heading => $body)
                    @continue(blank($body))
                    <div class="mt-10 rounded-2xl border border-navy/10 bg-white p-6">
                        <h2 class="section-title text-xl">{{ $heading }}</h2>
                        <div class="mt-4 text-base leading-relaxed text-ink-soft">{!! nl2br(e($body)) !!}</div>
                    </div>
                @endforeach
            </article>

            <aside class="space-y-6">
                @if ($story->program)
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Program Terkait</p>
                        <a href="{{ route('program.show', $story->program->slug) }}"
                           class="mt-2 block font-display text-lg font-semibold text-navy transition hover:text-terracotta">
                            {{ $story->program->title }}
                        </a>
                    </div>
                @endif

                @if ($story->organization)
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Organisasi</p>
                        <p class="mt-2 font-display text-lg font-semibold text-navy">{{ $story->organization->name }}</p>
                        @if ($story->organization->region)
                            <p class="meta">{{ $story->organization->region->name }}</p>
                        @endif
                    </div>
                @endif

                @if ($story->tags->count())
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Tag</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($story->tags as $tag)
                                <span class="chip">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($related->count())
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Cerita Lainnya</p>
                        <ul class="mt-4 space-y-4">
                            @foreach ($related as $other)
                                <li>
                                    <a href="{{ route('story.show', $other->slug) }}" class="group block">
                                        <p class="font-display text-sm font-semibold leading-snug text-navy group-hover:text-terracotta">{{ $other->title }}</p>
                                        <p class="mt-1 meta">{{ $other->categoryLabel() }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-2xl bg-navy p-7 text-white">
                    <p class="font-display text-xl font-semibold text-white">Bagikan cerita Anda</p>
                    <p class="mt-3 text-base leading-relaxed text-white/75">
                        Kisah Anda dapat menjadi inspirasi dan kekuatan bagi perempuan Maluku lainnya.
                    </p>
                    <a href="{{ route('contact') }}" class="btn btn-gold mt-6 w-full">Hubungi Kami</a>
                </div>
            </aside>
        </div>
    </section>

    @if ($related->count())
        <section class="section-soft section-y">
            <div class="container-page">
                <h2 class="section-title text-2xl md:text-3xl">Cerita Terkait</h2>
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $other)
                        <x-story-card :story="$other" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    @include('partials.media-attachments', ['media' => $media])
@endsection

