@extends('layouts.app')

@section('title', $region->name)

@section('content')
    <x-page-hero
        :eyebrow="$region->typeLabel()"
        :title="$region->name"
        :subtitle="$region->province && $region->province !== $region->name ? 'Provinsi '.$region->province : null"
        :image="$region->cover_image ? media_url($region->cover_image) : null"
        :breadcrumbs="[
            'Beranda' => route('home'),
            'Wilayah' => route('region.index'),
            $region->name => route('region.show', $region->slug),
        ]" />

    <section class="section-y">
        <div class="container-page grid gap-12 lg:grid-cols-[1.6fr_1fr]">
            <div>
                @if ($region->description)
                    <div class="prose-perwama text-base">
                        {!! $region->descriptionHtml() !!}
                    </div>
                @endif

                @if ($region->children->count())
                    <div class="mt-12" data-reveal>
                        <h2 class="section-title text-2xl">Wilayah Bagian</h2>
                        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($region->children as $child)
                                <a href="{{ route('region.show', $child->slug) }}"
                                   class="card flex flex-col p-6 transition hover:-translate-y-0.5">
                                    <p class="card-title font-display text-xl font-semibold text-navy">{{ $child->name }}</p>
                                    <p class="mt-1 text-sm tracking-[0.01em] text-ink-soft">{{ $child->typeLabel() }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($region->organizations->count())
                    <div class="mt-12" data-reveal>
                        <h2 class="section-title text-2xl">Organisasi</h2>
                        <div class="mt-6 space-y-4">
                            @foreach ($region->organizations as $organization)
                                <article class="rounded-2xl border border-navy/10 bg-white p-6">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <h3 class="card-title font-display text-xl font-semibold text-navy">{{ $organization->name }}</h3>
                                            <p class="mt-1 text-sm font-semibold tracking-[0.01em] text-terracotta">{{ $organization->typeLabel() }}</p>
                                        </div>
                                        @if ($organization->member_count)
                                            <span class="rounded-full bg-navy/5 px-3 py-1 text-sm font-semibold text-navy">
                                                {{ number_format($organization->member_count, 0, ',', '.') }} anggota
                                            </span>
                                        @endif
                                    </div>

                                    @if ($organization->leader_name)
                                        <p class="mt-4 meta">Ketua: <strong class="text-navy">{{ $organization->leader_name }}</strong></p>
                                    @endif
                                    @if ($organization->address)
                                        <p class="mt-1 meta">{{ $organization->address }}</p>
                                    @endif
                                    @if ($organization->description)
                                        <p class="mt-3 text-base leading-relaxed text-ink-soft">{!! nl2br(e(ringkas_isi($organization->description, null, 400))) !!}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ============ SIDEBAR ============ --}}
            <aside class="space-y-6">
                @if ($region->parent)
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Wilayah Induk</p>
                        <a href="{{ route('region.show', $region->parent->slug) }}"
                           class="mt-2 block font-display text-lg font-semibold text-navy transition hover:text-terracotta">
                            {{ $region->parent->name }}
                        </a>
                    </div>
                @endif

                @if ($provinces->count())
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Provinsi Lain</p>
                        <ul class="mt-4 space-y-3">
                            @foreach ($provinces as $province)
                                @continue($province->is($region))
                                <li>
                                    <a href="{{ route('region.show', $province->slug) }}"
                                       class="text-sm font-medium text-navy transition hover:text-terracotta">
                                        {{ $province->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($profile->map_embed)
                    <div class="overflow-hidden rounded-2xl border border-navy/10 bg-white">
                        <div class="aspect-video [&>iframe]:h-full [&>iframe]:w-full">
                            {!! $profile->map_embed !!}
                        </div>
                    </div>
                @endif

                <div class="rounded-2xl bg-navy p-7 text-white">
                    <p class="font-display text-xl font-semibold text-white">Ada kegiatan di {{ $region->name }}?</p>
                    <p class="mt-3 text-base leading-relaxed text-white/75">
                        Bagikan cerita dan dokumentasi kegiatan Anda kepada kami.
                    </p>
                    <a href="{{ route('contact') }}" class="btn btn-gold mt-6 w-full">Kirim Informasi</a>
                </div>
            </aside>
        </div>
    </section>

    @if ($region->activities->count())
        <section class="section-soft section-y">
            <div class="container-page">
                <h2 class="section-title text-2xl md:text-3xl">Kegiatan di {{ $region->name }}</h2>
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($region->activities as $activity)
                        <x-activity-card :activity="$activity" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($region->events->count())
        <section class="section-y">
            <div class="container-page">
                <h2 class="section-title text-2xl md:text-3xl">Agenda Mendatang</h2>
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($region->events as $event)
                        <a href="{{ route('event.show', $event->slug) }}" class="card flex h-full flex-col p-6">
                            <p class="font-display text-2xl font-semibold text-gold">{{ tanggal_id($event->start_date, 'd M') }}</p>
                            <h3 class="card-title mt-2 font-display text-xl font-semibold leading-snug text-navy">{{ $event->title }}</h3>
                            @if ($event->location)
                                <p class="mt-2 meta">{{ $event->location }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($region->stories->count())
        <section class="section-soft section-y">
            <div class="container-page">
                <h2 class="section-title text-2xl md:text-3xl">Cerita dari {{ $region->name }}</h2>
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($region->stories as $story)
                        <x-story-card :story="$story" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

