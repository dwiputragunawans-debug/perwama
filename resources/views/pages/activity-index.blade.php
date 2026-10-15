@extends('layouts.app')

@section('title', 'Kegiatan')

@section('content')
    <x-page-hero
        eyebrow="Kegiatan"
        title="Kegiatan & Agenda PERWAMA INA"
        subtitle="Dokumentasi kegiatan yang telah terlaksana serta agenda yang akan datang di seluruh wilayah."
        :breadcrumbs="['Beranda' => route('home'), 'Kegiatan' => route('activity.index')]" />

    <section class="section-y">
        <div class="container-page">
            {{-- ================= TAB ================= --}}
            <div class="flex flex-wrap gap-2 border-b border-navy/10 pb-4">
                <a href="{{ route('activity.index', ['tab' => 'kegiatan']) }}"
                   class="chip {{ $tab === 'agenda' ? '' : 'is-active' }}">Kegiatan</a>
                <a href="{{ route('activity.index', ['tab' => 'agenda']) }}"
                   class="chip {{ $tab === 'agenda' ? 'is-active' : '' }}">Agenda</a>
            </div>

            @if ($tab === 'agenda')
                {{-- ================= AGENDA MENDATANG ================= --}}
                <h2 class="section-title mt-10 text-2xl">Agenda Mendatang</h2>

                @if ($agendas->count())
                    <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($agendas as $event)
                            <a href="{{ route('event.show', $event->slug) }}" class="card flex h-full flex-col overflow-hidden">
                                @if ($event->poster)
                                    <img src="{{ media_url($event->poster) }}" alt="{{ $event->title }}"
                                         class="card-media-wide object-cover" loading="lazy">
                                @endif
                                <div class="flex flex-1 flex-col p-6">
                                    <p class="text-sm font-semibold tracking-[0.01em] text-gold">{{ $event->statusLabel() }}</p>
                                    <h3 class="card-title mt-2 font-display text-xl font-semibold leading-snug text-navy">{{ $event->title }}</h3>
                                    <p class="mt-3 meta">
                                        {{ tanggal_id($event->start_date, 'd F Y') }}@if ($event->start_time), {{ Str::substr($event->start_time, 0, 5) }} WIT @endif
                                    </p>
                                    @if ($event->location)
                                        <p class="mt-1 meta">{{ $event->location }}</p>
                                    @endif
                                    @if ($event->description)
                                        <p class="card-excerpt mt-3 text-base leading-relaxed text-ink-soft">{{ ringkas_isi($event->description, null, 180) }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="mt-8 rounded-2xl border border-navy/10 bg-white p-10 text-center meta">
                        Belum ada agenda mendatang. Silakan cek kembali nanti.
                    </p>
                @endif

                @if ($pastEvents->count())
                    <h2 class="section-title mt-16 text-2xl">Agenda Terlaksana</h2>
                    <ul class="mt-6 divide-y divide-navy/10 overflow-hidden rounded-2xl border border-navy/10 bg-white">
                        @foreach ($pastEvents as $event)
                            <li>
                                <a href="{{ route('event.show', $event->slug) }}"
                                   class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 transition hover:bg-cream">
                                    <span class="font-medium text-navy">{{ $event->title }}</span>
                                    <span class="meta">{{ tanggal_id($event->start_date, 'd M Y') }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @else
                {{-- ================= FILTER KEGIATAN ================= --}}
                <div class="mt-8 flex flex-wrap items-center gap-2">
                    <a href="{{ route('activity.index', ['tab' => 'kegiatan']) }}"
                       class="chip {{ $activeCategory ? '' : 'is-active' }}">Semua</a>
                    @foreach ($categories as $key => $label)
                        <a href="{{ route('activity.index', ['tab' => 'kegiatan', 'kategori' => $key]) }}"
                           class="chip {{ $activeCategory === $key ? 'is-active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <p class="mt-6 meta">
                    Menampilkan {{ $activities->count() }} dari {{ $activities->total() }} kegiatan.
                </p>

                @if ($activities->count())
                    {{-- Kegiatan terbaru tampil besar sebagai pembuka daftar. --}}
                    <div class="mt-14" data-reveal>
                        <x-activity-card :activity="$activities->first()" featured />
                    </div>

                    @if ($activities->count() > 1)
                        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($activities->skip(1) as $activity)
                                <div data-reveal>
                                    <x-activity-card :activity="$activity" />
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-14">{{ $activities->links() }}</div>
                @else
                    <p class="mt-8 rounded-2xl border border-navy/10 bg-white p-10 text-center meta">
                        Tidak ada kegiatan yang cocok dengan filter tersebut.
                    </p>
                @endif
            @endif
        </div>
    </section>

    {{-- ================= DOKUMENTASI ================= --}}
    @if ($galleries->count())
        <section class="section-soft section-y">
            <div class="container-page">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="section-title text-2xl md:text-3xl">Dokumentasi Kegiatan</h2>
                    <a href="{{ route('gallery.index') }}" class="text-sm font-semibold text-terracotta hover:underline">Lihat semua galeri &rarr;</a>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($galleries as $gallery)
                        <x-gallery-card :gallery="$gallery" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

