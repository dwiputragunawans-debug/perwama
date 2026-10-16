@extends('layouts.app')

@section('title', 'Program')

@section('content')
    <x-page-hero
        eyebrow="Program"
        title="Program & Kegiatan PERWAMA INA"
        subtitle="Berbagai inisiatif pemberdayaan perempuan Maluku di bidang pendidikan, kesehatan, ekonomi, sosial, dan budaya."
        :breadcrumbs="['Beranda' => route('home'), 'Program' => route('program.index')]" />

    <section class="section-y">
        <div class="container-page">
            {{-- Filter kategori --}}
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('program.index') }}"
                   class="chip {{ $activeCategory ? '' : 'is-active' }}">Semua</a>

                @foreach ($categories as $category)
                    <a href="{{ route('program.index', ['kategori' => $category->slug]) }}"
                       class="chip {{ $activeCategory === $category->slug ? 'is-active' : '' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            {{-- Pencarian --}}
            <form action="{{ route('program.index') }}" method="GET" class="mt-6 flex flex-wrap gap-3">
                @if ($activeCategory)
                    <input type="hidden" name="kategori" value="{{ $activeCategory }}">
                @endif
                <label for="program-q" class="sr-only">Cari program</label>
                <input id="program-q" type="search" name="q" value="{{ $search }}" placeholder="Cari nama program…"
                       class="field-input sm:max-w-sm">
                <button type="submit" class="btn btn-navy">Cari</button>
                @if ($search || $activeCategory)
                    <a href="{{ route('program.index') }}" class="btn btn-quiet">Reset</a>
                @endif
            </form>

            <p class="mt-6 meta">
                Menampilkan {{ $programs->count() }} dari {{ $programs->total() }} program.
            </p>

            @if ($programs->count())
                {{-- Program pertama ditampilkan besar agar daftar tidak seragam. --}}
                <div class="mt-14" data-reveal>
                    <x-program-card :program="$programs->first()" featured />
                </div>

                @if ($programs->count() > 1)
                    <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($programs->skip(1) as $program)
                            <div data-reveal>
                                <x-program-card :program="$program" />
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($programs->hasPages())
                    {{-- Jangan render pembungkus kosong saat hanya ada satu halaman:
                         div ber-margin mt-14 tetap menyumbang 3.5rem ruang kosong. --}}
                    <div class="mt-14">
                        {{ $programs->links() }}
                    </div>
                @endif
            @else
                <div class="mt-10 rounded-2xl border border-navy/10 bg-white p-12 text-center">
                    <p class="font-display text-2xl font-semibold text-navy">Belum ada program</p>
                    <p class="mt-2 meta">
                        Tidak ada program yang cocok dengan filter yang Anda pilih.
                    </p>
                    <a href="{{ route('program.index') }}" class="btn btn-navy mt-6">Lihat semua program</a>
                </div>
            @endif
        </div>
    </section>
@endsection
