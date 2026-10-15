@extends('layouts.app')

@section('title', 'Galeri')

@section('content')
    <x-page-hero
        eyebrow="Galeri"
        title="Galeri Foto & Video"
        subtitle="Dokumentasi visual kegiatan, program, dan momen kebersamaan PERWAMA INA di seluruh wilayah."
        :breadcrumbs="['Beranda' => route('home'), 'Galeri' => route('gallery.index')]" />

    <section class="section-y">
        <div class="container-page">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('gallery.index') }}" class="chip {{ $activeAlbum ? '' : 'is-active' }}">Semua Album</a>
                @foreach ($albums as $key => $label)
                    <a href="{{ route('gallery.index', ['album' => $key]) }}"
                       class="chip {{ $activeAlbum === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <p class="mt-6 meta">
                Menampilkan {{ $galleries->count() }} dari {{ $galleries->total() }} album.
            </p>

            @if ($galleries->count())
                {{-- Album pertama lebih lebar agar susunan terasa hidup. --}}
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($galleries as $gallery)
                        <div class="{{ $loop->first ? 'lg:col-span-2' : '' }}" data-reveal>
                            <x-gallery-card :gallery="$gallery" :wide="$loop->first" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-14">{{ $galleries->links() }}</div>
            @else
                <div class="mt-10 rounded-2xl border border-navy/10 bg-white p-12 text-center">
                    <p class="font-display text-2xl font-semibold text-navy">Belum ada album</p>
                    <p class="mt-2 meta">Album galeri untuk kategori ini belum tersedia.</p>
                    <a href="{{ route('gallery.index') }}" class="btn btn-navy mt-6">Lihat semua album</a>
                </div>
            @endif
        </div>
    </section>

    {{-- ================= VIDEO ================= --}}
    @if ($videos->count())
        <section class="section-soft section-y">
            <div class="container-page">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="section-eyebrow">Video</p>
                        <h2 class="section-title mt-2 text-2xl md:text-3xl">Video Kegiatan</h2>
                    </div>
                    <a href="{{ route('video.index') }}" class="text-sm font-semibold text-terracotta hover:underline">Lihat semua video &rarr;</a>
                </div>

                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($videos as $video)
                        <x-video-card :video="$video" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
