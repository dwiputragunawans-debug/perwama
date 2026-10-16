@extends('layouts.app')

@section('title', 'Video')

@section('content')
    <x-page-hero
        eyebrow="Video"
        title="Video PERWAMA INA"
        subtitle="Kumpulan video profil organisasi, dokumentasi kegiatan, dan materi sosialisasi."
        :breadcrumbs="['Beranda' => route('home'), 'Galeri' => route('gallery.index'), 'Video' => route('video.index')]" />

    <section class="section-y">
        <div class="container-page">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('video.index') }}" class="chip {{ $activeCategory ? '' : 'is-active' }}">Semua</a>
                @foreach ($categories as $key => $label)
                    <a href="{{ route('video.index', ['kategori' => $key]) }}"
                       class="chip {{ $activeCategory === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <p class="mt-6 meta">
                Menampilkan {{ $videos->count() }} dari {{ $videos->total() }} video.
            </p>

            @if ($videos->count())
                <div class="mt-14" data-reveal>
                    <x-video-card :video="$videos->first()" featured />
                </div>

                @if ($videos->count() > 1)
                    <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($videos->skip(1) as $video)
                            <div data-reveal>
                                <x-video-card :video="$video" />
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($videos->hasPages())
                    {{-- Jangan render pembungkus kosong saat hanya ada satu halaman:
                         div ber-margin mt-14 tetap menyumbang 3.5rem ruang kosong. --}}
                    <div class="mt-14">{{ $videos->links() }}</div>
                @endif
            @else
                <div class="mt-10 rounded-2xl border border-navy/10 bg-white p-12 text-center">
                    <p class="font-display text-2xl font-semibold text-navy">Belum ada video</p>
                    <p class="mt-2 meta">Video untuk kategori ini belum tersedia.</p>
                    <a href="{{ route('video.index') }}" class="btn btn-navy mt-6">Lihat semua video</a>
                </div>
            @endif
        </div>
    </section>
@endsection
