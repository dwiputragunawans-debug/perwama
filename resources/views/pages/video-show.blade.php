@extends('layouts.app')

@section('title', $video->title)

@section('content')
    <x-page-hero
        :eyebrow="$video->categoryLabel()"
        :title="$video->title"
        :subtitle="$video->description ? ringkas_isi($video->description, null, 230) : null"
        :image="$video->thumbnailUrl()"
        :breadcrumbs="[
            'Beranda' => route('home'),
            'Video' => route('video.index'),
            Str::limit($video->title, 40) => route('video.show', $video->slug),
        ]" />

    <section class="section-y">
        <div class="container-page grid gap-12 lg:grid-cols-[1.6fr_1fr]">
            <article>
                @if ($video->embedUrl())
                    <div class="overflow-hidden rounded-2xl border border-navy/10 bg-navy-deep">
                        <div class="aspect-video [&>iframe]:h-full [&>iframe]:w-full">
                            <iframe src="{{ $video->embedUrl() }}" title="{{ $video->title }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen loading="lazy"></iframe>
                        </div>
                    </div>
                @elseif ($video->thumbnailUrl())
                    <img src="{{ $video->thumbnailUrl() }}" alt="{{ $video->title }}"
                         class="w-full rounded-2xl object-cover" loading="lazy">
                @endif

                <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-2 meta">
                    <span class="rounded-full bg-navy/5 px-3 py-1 text-sm font-semibold text-navy">{{ $video->categoryLabel() }}</span>
                    <time datetime="{{ optional($video->published_at)->toDateString() }}">{{ tanggal_id($video->published_at, 'd F Y') }}</time>
                </div>

                @if ($video->description)
                    <div class="prose-perwama mt-8 text-base">
                        {{-- Isi dari RichEditor dibersihkan lebih dulu (lihat App\Support\HtmlSanitizer). --}}
                        {!! html_aman($video->description) !!}
                    </div>
                @endif
            </article>

            <aside class="space-y-6">
                <div class="rounded-2xl border border-navy/10 bg-white p-6">
                    <p class="text-sm font-semibold tracking-[0.01em] text-terracotta">Tonton & Bagikan</p>
                    <p class="mt-3 text-base leading-relaxed text-ink-soft">
                        Anda dapat menonton video ini langsung melalui kanal resmi kami.
                    </p>
                    @if ($video->url)
                        <a href="{{ $video->url }}" target="_blank" rel="noopener" class="btn btn-navy mt-5 w-full">
                            Buka di kanal asal
                        </a>
                    @endif
                    <a href="{{ route('gallery.index') }}" class="btn btn-outline mt-3 w-full">Lihat galeri foto</a>
                </div>

                @if ($others->count())
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Video Lainnya</p>
                        <ul class="mt-4 space-y-5">
                            @foreach ($others as $other)
                                <li>
                                    <a href="{{ route('video.show', $other->slug) }}" class="group flex gap-4">
                                        @if ($other->thumbnailUrl())
                                            <img src="{{ $other->thumbnailUrl() }}" alt="{{ $other->title }}"
                                                 class="h-14 w-24 flex-none rounded-lg object-cover" loading="lazy">
                                        @endif
                                        <span class="min-w-0">
                                            <span class="block font-display text-sm font-semibold leading-snug text-navy group-hover:text-terracotta">{{ $other->title }}</span>
                                            <span class="mt-1 block meta">{{ $other->categoryLabel() }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </section>
    @include('partials.media-attachments', ['media' => $media])
@endsection
