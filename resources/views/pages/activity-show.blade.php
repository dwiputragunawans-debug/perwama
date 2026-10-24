@extends('layouts.app')

@section('title', $activity->title)
@section('meta_description', ringkas($activity->meta_description ?: $activity->excerpt ?: $activity->description, 155))

@section('content')
    <x-page-hero
        :eyebrow="$activity->categoryLabel()"
        :title="$activity->title"
        :subtitle="$activity->excerpt"
        :image="$activity->cover_image ? media_url($activity->cover_image) : null"
        :breadcrumbs="[
            'Beranda' => route('home'),
            'Kegiatan' => route('activity.index'),
            Str::limit($activity->title, 40) => route('activity.show', $activity->slug),
        ]" />

    <section class="section-y">
        <div class="container-page grid gap-12 lg:grid-cols-[1.6fr_1fr]">
            <article>
                <dl class="grid gap-4 rounded-2xl border border-navy/10 bg-white p-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Tanggal</dt>
                        <dd class="mt-1 font-display text-base font-semibold text-navy">
                            {{ tanggal_id($activity->activity_date, 'd F Y') ?: '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Wilayah</dt>
                        <dd class="mt-1 font-display text-base font-semibold text-navy">{{ $activity->region->name ?? 'Nasional' }}</dd>
                    </div>
                    @if ($activity->location)
                        <div>
                            <dt class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Lokasi</dt>
                            <dd class="mt-1 font-display text-base font-semibold text-navy">{{ $activity->location }}</dd>
                        </div>
                    @endif
                    @if ($activity->participant_count)
                        <div>
                            <dt class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Peserta</dt>
                            <dd class="mt-1 font-display text-base font-semibold text-navy">{{ number_format((int) $activity->participant_count, 0, ',', '.') }} orang</dd>
                        </div>
                    @endif
                </dl>

                @if ($activity->description)
                    <div class="prose-perwama mt-10 text-base">
                        {{-- Isi dari RichEditor dibersihkan lebih dulu (lihat App\Support\HtmlSanitizer). --}}
                        {!! html_aman($activity->description) !!}
                    </div>
                @elseif ($activity->excerpt)
                    <div class="prose-perwama mt-10 text-base">
                        <p>{{ $activity->excerpt }}</p>
                    </div>
                @endif

                @if ($activity->gallery && $activity->gallery->items->count())
                    <div class="mt-12">
                        <h2 class="section-title text-2xl">Dokumentasi</h2>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($activity->gallery->items as $item)
                                @if ($item->file && ! $item->isVideo())
                                    <a href="{{ media_url($item->file) }}" data-lightbox="{{ media_url($item->file) }}"
                                       data-lightbox-group="activity-gallery-{{ $activity->gallery->getKey() }}"
                                       data-lightbox-caption="{{ $item->caption }}"
                                       class="block overflow-hidden rounded-xl">
                                        <img src="{{ media_url($item->thumbnail ?: $item->file) }}"
                                             alt="{{ $item->caption ?: $activity->title }}"
                                             class="card-media transition duration-500 hover:scale-105" loading="lazy">
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>

            <aside class="space-y-6">
                @if ($activity->program)
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Program Terkait</p>
                        <a href="{{ route('program.show', $activity->program->slug) }}"
                           class="mt-2 block font-display text-lg font-semibold text-navy transition hover:text-terracotta">
                            {{ $activity->program->title }}
                        </a>
                    </div>
                @endif

                @if ($activity->event)
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Agenda Induk</p>
                        <a href="{{ route('event.show', $activity->event->slug) }}"
                           class="mt-2 block font-display text-lg font-semibold text-navy transition hover:text-terracotta">
                            {{ $activity->event->title }}
                        </a>
                        @if ($activity->event->start_date)
                            <p class="mt-1 meta">{{ tanggal_id($activity->event->start_date, 'd F Y') }}</p>
                        @endif
                    </div>
                @endif

                @if ($activity->speaker)
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-terracotta">Narasumber</p>
                        <p class="mt-2 text-base leading-relaxed text-ink-soft">{{ $activity->speaker }}</p>
                    </div>
                @endif

                @if ($activity->tags->count())
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Tag</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($activity->tags as $tag)
                                <span class="chip">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($regions->count())
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Wilayah</p>
                        <ul class="mt-4 space-y-3">
                            @foreach ($regions as $region)
                                <li>
                                    <a href="{{ route('region.show', $region->slug) }}"
                                       class="text-sm font-medium text-navy transition hover:text-terracotta">{{ $region->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    @if ($related->count())
        <section class="section-soft section-y">
            <div class="container-page">
                <h2 class="section-title text-2xl md:text-3xl">Kegiatan Lainnya</h2>
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-activity-card :activity="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    @include('partials.media-attachments', ['media' => $media])
@endsection

