@extends('layouts.app')

@section('title', 'Pencarian')

@section('content')
    <x-page-hero
        eyebrow="Pencarian"
        title="{{ $term !== '' ? 'Hasil pencarian: “'.$term.'”' : 'Cari di situs PERWAMA INA' }}"
        subtitle="Telusuri program, kegiatan, cerita, dan berita yang tersedia di situs kami."
        :breadcrumbs="['Beranda' => route('home'), 'Pencarian' => route('search')]" />

    <section class="section-y">
        <div class="container-page">
            <form action="{{ route('search') }}" method="GET" class="flex flex-wrap gap-3">
                <label for="q" class="sr-only">Kata kunci</label>
                <input id="q" type="search" name="q" value="{{ $term }}" placeholder="Masukkan kata kunci…"
                       class="field-input sm:max-w-md">
                <button type="submit" class="btn btn-navy">Cari</button>
            </form>

            @php
                $total = $programs->count() + $activities->count() + $stories->count() + $newsItems->count();
            @endphp

            @if ($term === '')
                <p class="mt-10 rounded-2xl border border-navy/10 bg-white p-10 text-center meta">
                    Silakan masukkan kata kunci untuk mulai mencari.
                </p>
            @elseif ($total === 0)
                <div class="mt-10 rounded-2xl border border-navy/10 bg-white p-12 text-center">
                    <p class="font-display text-2xl font-semibold text-navy">Tidak ada hasil ditemukan</p>
                    <p class="mt-2 meta">
                        Tidak ditemukan konten yang cocok dengan “{{ $term }}”. Coba kata kunci lain.
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('program.index') }}" class="btn btn-outline">Lihat program</a>
                        <a href="{{ route('news.index') }}" class="btn btn-outline">Lihat berita</a>
                    </div>
                </div>
            @else
                <p class="mt-8 meta">
                    Ditemukan {{ $total }} hasil untuk “{{ $term }}”.
                </p>

                <div class="mt-8 space-y-14">
                    @if ($programs->isNotEmpty())
                        <div>
                            <div class="flex items-end justify-between gap-3">
                                <h2 class="section-title text-xl md:text-2xl">Program</h2>
                                <a href="{{ route('program.index', ['q' => $term]) }}" class="text-sm font-semibold text-terracotta hover:underline">
                                    Lihat semua &rarr;
                                </a>
                            </div>
                            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($programs as $program)
                                    <x-program-card :program="$program" />
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($activities->isNotEmpty())
                        <div>
                            <div class="flex items-end justify-between gap-3">
                                <h2 class="section-title text-xl md:text-2xl">Kegiatan</h2>
                                <a href="{{ route('activity.index') }}" class="text-sm font-semibold text-terracotta hover:underline">
                                    Lihat semua &rarr;
                                </a>
                            </div>
                            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($activities as $activity)
                                    <x-activity-card :activity="$activity" />
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($stories->isNotEmpty())
                        <div>
                            <div class="flex items-end justify-between gap-3">
                                <h2 class="section-title text-xl md:text-2xl">Cerita</h2>
                                <a href="{{ route('story.index') }}" class="text-sm font-semibold text-terracotta hover:underline">
                                    Lihat semua &rarr;
                                </a>
                            </div>
                            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($stories as $story)
                                    <x-story-card :story="$story" />
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($newsItems->isNotEmpty())
                        <div>
                            <div class="flex items-end justify-between gap-3">
                                <h2 class="section-title text-xl md:text-2xl">Berita</h2>
                                <a href="{{ route('news.index', ['q' => $term]) }}" class="text-sm font-semibold text-terracotta hover:underline">
                                    Lihat semua &rarr;
                                </a>
                            </div>
                            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($newsItems as $item)
                                    <x-news-card :item="$item" />
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
