@extends('layouts.app')

@section('title', 'Publikasi')

@section('content')
    <x-page-hero
        eyebrow="Publikasi"
        title="Publikasi & Dokumen PERWAMA INA"
        subtitle="Laporan, buku, modul, kajian, dan materi pendidikan yang dapat diunduh secara bebas."
        :breadcrumbs="['Beranda' => route('home'), 'Publikasi' => route('publication.index')]" />

    <section class="section-y">
        <div class="container-page">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('publication.index') }}" class="chip {{ $activeCategory ? '' : 'is-active' }}">Semua</a>
                @foreach ($categories as $key => $label)
                    <a href="{{ route('publication.index', ['kategori' => $key]) }}"
                       class="chip {{ $activeCategory === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <p class="mt-6 meta">
                Menampilkan {{ $publications->count() }} dari {{ $publications->total() }} publikasi.
            </p>

            @if ($publications->count())
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($publications as $publication)
                        <article class="card flex h-full flex-col" data-reveal>
                            <a href="{{ route('publication.show', $publication->slug) }}" class="block overflow-hidden">
                                @if ($publication->cover_image)
                                    <img src="{{ media_url($publication->cover_image) }}" alt="{{ $publication->title }}"
                                         class="card-media-wide object-cover transition duration-500 hover:scale-105" loading="lazy">
                                @else
                                    <div class="hero-pattern flex h-48 items-center justify-center bg-navy">
                                        <span class="font-display text-sm font-semibold tracking-[0.2em] text-white/80">
                                            {{ $publication->categoryLabel() }}
                                        </span>
                                    </div>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col p-6">
                                <p class="text-xs font-semibold tracking-[0.01em] text-gold">
                                    {{ $publication->categoryLabel() }}@if ($publication->year) · {{ $publication->year }} @endif
                                </p>

                                <h2 class="card-title mt-3 font-display text-xl font-semibold leading-snug text-navy">
                                    <a href="{{ route('publication.show', $publication->slug) }}" class="transition hover:text-terracotta">
                                        {{ $publication->title }}
                                    </a>
                                </h2>

                                @if ($publication->description)
                                    <p class="card-excerpt mt-3 text-base leading-relaxed text-ink-soft">{{ ringkas_isi($publication->description, null, 180) }}</p>
                                @endif

                                <div class="mt-auto flex flex-wrap items-center gap-x-4 gap-y-2 pt-5 meta">
                                    @if ($publication->page_count)
                                        <span>{{ $publication->page_count }} halaman</span>
                                    @endif
                                    @if ($publication->file_size)
                                        <span>{{ $publication->fileSizeLabel() }}</span>
                                    @endif
                                    <span>{{ number_format((int) $publication->download_count, 0, ',', '.') }} unduhan</span>
                                </div>

                                @if ($publication->file)
                                    <a href="{{ media_url($publication->file) }}" target="_blank" rel="noopener" class="btn btn-navy mt-5">
                                        Unduh
                                    </a>
                                @else
                                    <a href="{{ route('publication.show', $publication->slug) }}" class="btn btn-outline mt-5">
                                        Lihat detail
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($publications->hasPages())
                    {{-- Jangan render pembungkus kosong saat hanya ada satu halaman:
                         div ber-margin mt-12 tetap menyumbang 3rem ruang kosong. --}}
                    <div class="mt-12">{{ $publications->links() }}</div>
                @endif
            @else
                <div class="mt-10 rounded-2xl border border-navy/10 bg-white p-12 text-center">
                    <p class="font-display text-2xl font-semibold text-navy">Belum ada publikasi</p>
                    <p class="mt-2 meta">Publikasi untuk kategori ini belum tersedia.</p>
                    <a href="{{ route('publication.index') }}" class="btn btn-navy mt-6">Lihat semua publikasi</a>
                </div>
            @endif
        </div>
    </section>
@endsection
