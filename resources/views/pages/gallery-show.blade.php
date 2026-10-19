@extends('layouts.app')

@section('title', $gallery->title)

@section('content')
    <x-page-hero
        :eyebrow="$gallery->albumLabel()"
        :title="$gallery->title"
        :subtitle="$gallery->event_date ? tanggal_id($gallery->event_date, 'd F Y') : null"
        :image="$gallery->cover_image ? media_url($gallery->cover_image) : null"
        :breadcrumbs="[
            'Beranda' => route('home'),
            'Galeri' => route('gallery.index'),
            Str::limit($gallery->title, 40) => route('gallery.show', $gallery->slug),
        ]" />

    <section class="section-y">
        <div class="container-page">
            @if ($gallery->description)
                <div class="prose-perwama max-w-3xl text-base">
                    <p>{{ $gallery->description }}</p>
                </div>
            @endif

            <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 meta">
                <span>{{ $gallery->items->count() }} media</span>
                @if ($gallery->event_date)
                    <span>{{ tanggal_id($gallery->event_date, 'd F Y') }}</span>
                @endif
                @if ($gallery->region)
                    <a href="{{ route('region.show', $gallery->region->slug) }}" class="hover:text-terracotta">{{ $gallery->region->name }}</a>
                @endif
                @if ($gallery->program)
                    <a href="{{ route('program.show', $gallery->program->slug) }}" class="hover:text-terracotta">{{ $gallery->program->title }}</a>
                @endif
            </div>

            @if ($gallery->items->count())
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($gallery->items as $item)
                        @if ($item->isVideo() && $item->embedUrl())
                            <div class="overflow-hidden rounded-2xl border border-navy/10 bg-navy">
                                <div class="aspect-video [&>iframe]:h-full [&>iframe]:w-full">
                                    <iframe src="{{ $item->embedUrl() }}" title="{{ $item->caption ?: $gallery->title }}"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen loading="lazy"></iframe>
                                </div>
                                @if ($item->caption)
                                    <p class="px-4 py-3 text-sm text-white/80">{{ $item->caption }}</p>
                                @endif
                            </div>
                        @elseif ($item->file)
                            <figure class="overflow-hidden rounded-2xl border border-navy/10 bg-white">
                                <a href="{{ media_url($item->file) }}" data-lightbox="{{ media_url($item->file) }}"
                                   class="block overflow-hidden">
                                    <img src="{{ media_url($item->thumbnail ?: $item->file) }}"
                                         alt="{{ $item->caption ?: $gallery->title }}"
                                         class="card-media object-cover transition duration-500 hover:scale-105" loading="lazy">
                                </a>
                                @if ($item->caption)
                                    <figcaption class="px-4 py-3 meta">{{ $item->caption }}</figcaption>
                                @endif
                            </figure>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="mt-10 rounded-2xl border border-navy/10 bg-white p-10 text-center meta">
                    Album ini belum memiliki media.
                </p>
            @endif
        </div>
    </section>

    @if ($others->count())
        <section class="section-soft section-y">
            <div class="container-page">
                <h2 class="section-title text-2xl md:text-3xl">Album Lainnya</h2>
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($others as $other)
                        <x-gallery-card :gallery="$other" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
