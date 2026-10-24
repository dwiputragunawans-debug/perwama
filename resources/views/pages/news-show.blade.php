@extends('layouts.app')

@section('title', $news->title)

@section('content')
    <x-page-hero
        :eyebrow="$news->typeLabel().' · '.$news->categoryLabel()"
        :title="$news->title"
        :subtitle="$news->excerpt"
        :image="$news->cover_image ? media_url($news->cover_image) : null"
        :breadcrumbs="[
            'Beranda' => route('home'),
            'Berita' => route('news.index'),
            Str::limit($news->title, 40) => route('news.show', $news->slug),
        ]" />

    <section class="section-y">
        <div class="container-page grid gap-12 lg:grid-cols-[1.6fr_1fr]">
            <article>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 meta">
                    <span>{{ tanggal_id($news->published_at, 'd F Y') }}</span>
                    @if ($news->author)
                        <span>Oleh <strong class="text-navy">{{ $news->author }}</strong></span>
                    @endif
                    @if ($news->region)
                        <span>{{ $news->region->name }}</span>
                    @endif
                    <span class="rounded-full bg-navy/5 px-3 py-1 text-sm font-semibold text-navy">{{ $news->typeLabel() }}</span>
                </div>

                @if ($news->cover_image)
                    <img src="{{ media_url($news->cover_image) }}" alt="{{ $news->title }}"
                         class="mt-8 w-full rounded-2xl object-cover" loading="lazy">
                @endif

                @if ($news->excerpt)
                    <p class="mt-8 border-l-4 border-gold pl-5 font-display text-lg italic leading-relaxed text-navy">
                        {{ $news->excerpt }}
                    </p>
                @endif

                <div class="prose-perwama mt-8 text-base">
                    {{-- Isi dari RichEditor dibersihkan lebih dulu (lihat App\Support\HtmlSanitizer). --}}
                    {!! html_aman($news->content) ?: '<p>'.e($news->excerpt).'</p>' !!}
                </div>

                @if ($news->source)
                    <p class="mt-8 rounded-xl border border-navy/10 bg-white px-5 py-4 meta">
                        Sumber: <strong class="text-navy">{{ $news->source }}</strong>
                    </p>
                @endif

                @if ($news->tags->count())
                    <div class="mt-8 flex flex-wrap items-center gap-2">
                        <span class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Tag:</span>
                        @foreach ($news->tags as $tag)
                            <span class="chip">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </article>

            <aside class="space-y-6">
                @if ($news->program)
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Program Terkait</p>
                        <a href="{{ route('program.show', $news->program->slug) }}"
                           class="mt-2 block font-display text-lg font-semibold text-navy transition hover:text-terracotta">
                            {{ $news->program->title }}
                        </a>
                    </div>
                @endif

                @if ($related->count())
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Berita Lainnya</p>
                        <ul class="mt-4 space-y-5">
                            @foreach ($related as $other)
                                <li>
                                    <a href="{{ route('news.show', $other->slug) }}" class="group block">
                                        <p class="font-display text-sm font-semibold leading-snug text-navy group-hover:text-terracotta">{{ $other->title }}</p>
                                        <p class="mt-1 meta">{{ tanggal_id($other->published_at, 'd M Y') }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-2xl bg-navy p-7 text-white">
                    <p class="font-display text-xl font-semibold text-white">Ikuti kabar terbaru kami</p>
                    <p class="mt-3 text-base leading-relaxed text-white/75">
                        Dapatkan informasi kegiatan dan program terbaru PERWAMA INA.
                    </p>
                    <a href="{{ route('join') }}" class="btn btn-gold mt-6 w-full">Bergabung</a>
                </div>
            </aside>
        </div>
    </section>
    @include('partials.media-attachments', ['media' => $media])
@endsection
