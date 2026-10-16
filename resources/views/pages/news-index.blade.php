@extends('layouts.app')

@section('title', 'Berita')

@section('content')
    <x-page-hero
        eyebrow="Berita"
        title="Berita & Informasi PERWAMA INA"
        subtitle="Kabar terbaru seputar organisasi, kegiatan, program, serta isu perempuan dari seluruh Indonesia."
        :breadcrumbs="['Beranda' => route('home'), 'Berita' => route('news.index')]" />

    {{-- ================= BERITA UNGGULAN ================= --}}
    @if ($featured)
        <section class="section-y">
            <div class="container-page">
                <article class="grid overflow-hidden rounded-3xl border border-navy/10 bg-white lg:grid-cols-2">
                    <div class="relative min-h-[16rem]">
                        @if ($featured->cover_image)
                            <img src="{{ media_url($featured->cover_image) }}" alt="{{ $featured->title }}"
                                 class="h-full w-full object-cover" loading="lazy">
                        @else
                            <div class="hero-pattern h-full min-h-[16rem] bg-navy"></div>
                        @endif
                    </div>

                    <div class="p-8 md:p-12">
                        <p class="flex flex-wrap items-center gap-3 text-sm font-semibold tracking-[0.01em]">
                            <span class="rounded-full bg-terracotta/10 px-3 py-1 text-terracotta">{{ $featured->typeLabel() }}</span>
                            <span class="text-ink-soft">{{ tanggal_id($featured->published_at, 'd F Y') }}</span>
                        </p>
                        <h2 class="mt-4 font-display text-2xl font-semibold leading-snug text-navy md:text-3xl">
                            <a href="{{ route('news.show', $featured->slug) }}" class="transition hover:text-terracotta">
                                {{ $featured->title }}
                            </a>
                        </h2>
                        <p class="card-excerpt mt-4 text-base leading-relaxed text-ink-soft">
                            {{ ringkas_isi($featured->excerpt, $featured->content, 260) }}
                        </p>
                        <a href="{{ route('news.show', $featured->slug) }}" class="btn btn-navy mt-8">Baca selengkapnya</a>
                    </div>
                </article>
            </div>
        </section>
    @endif

    {{-- ================= DAFTAR BERITA ================= --}}
    <section class="{{ $featured ? 'section-soft' : '' }} section-y">
        <div class="container-page">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('news.index') }}" class="chip {{ $activeCategory || $activeType ? '' : 'is-active' }}">Semua</a>
                @foreach ($types as $key => $label)
                    <a href="{{ route('news.index', ['tipe' => $key]) }}"
                       class="chip {{ $activeType === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
                @foreach ($categories as $key => $label)
                    <a href="{{ route('news.index', ['kategori' => $key]) }}"
                       class="chip {{ $activeCategory === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <form action="{{ route('news.index') }}" method="GET" class="mt-6 flex flex-wrap gap-3">
                @if ($activeCategory)
                    <input type="hidden" name="kategori" value="{{ $activeCategory }}">
                @endif
                @if ($activeType)
                    <input type="hidden" name="tipe" value="{{ $activeType }}">
                @endif
                <label for="news-q" class="sr-only">Cari berita</label>
                <input id="news-q" type="search" name="q" value="{{ $search }}" placeholder="Cari berita…"
                       class="field-input sm:max-w-sm">
                <button type="submit" class="btn btn-navy">Cari</button>
                @if ($search || $activeCategory || $activeType)
                    <a href="{{ route('news.index') }}" class="btn btn-quiet">Reset</a>
                @endif
            </form>

            <p class="mt-6 meta">
                Menampilkan {{ $newsItems->count() }} dari {{ $newsItems->total() }} berita.
            </p>

            @if ($newsItems->count())
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($newsItems as $item)
                        <x-news-card :item="$item" />
                    @endforeach
                </div>
                @if ($newsItems->hasPages())
                    {{-- Jangan render pembungkus kosong saat hanya ada satu halaman:
                         div ber-margin mt-12 tetap menyumbang 3rem ruang kosong. --}}
                    <div class="mt-12">{{ $newsItems->links() }}</div>
                @endif
            @else
                <div class="mt-10 rounded-2xl border border-navy/10 bg-white p-12 text-center">
                    <p class="font-display text-2xl font-semibold text-navy">Berita tidak ditemukan</p>
                    <p class="mt-2 meta">Coba ubah kata kunci atau pilih kategori lain.</p>
                    <a href="{{ route('news.index') }}" class="btn btn-navy mt-6">Lihat semua berita</a>
                </div>
            @endif
        </div>
    </section>
@endsection
