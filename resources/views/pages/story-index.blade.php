@extends('layouts.app')

@section('title', 'Cerita')

@section('content')
    <x-page-hero
        eyebrow="Cerita"
        title="Kisah Inspiratif Perempuan Maluku"
        subtitle="Cerita nyata tentang ketangguhan, perjuangan, dan keberhasilan perempuan Maluku di berbagai bidang."
        :breadcrumbs="['Beranda' => route('home'), 'Cerita' => route('story.index')]" />

    {{-- ================= CERITA UNGGULAN ================= --}}
    @if ($featured)
        <section class="section-y">
            <div class="container-page">
                <div class="grid overflow-hidden rounded-3xl border border-navy/10 bg-white lg:grid-cols-2">
                    <div class="relative min-h-[18rem]">
                        @if ($featured->cover_image || $featured->person_photo)
                            <img src="{{ media_url($featured->cover_image ?: $featured->person_photo) }}"
                                 alt="{{ $featured->title }}" class="h-full w-full object-cover" loading="lazy">
                        @else
                            <div class="hero-pattern h-full min-h-[18rem] bg-navy"></div>
                        @endif
                    </div>

                    <div class="p-8 md:p-12">
                        <p class="section-eyebrow">Cerita Pilihan</p>
                        <h2 class="mt-3 font-display text-2xl font-semibold leading-snug text-navy md:text-3xl">
                            {{ $featured->title }}
                        </h2>

                        @if ($featured->person_name)
                            <p class="mt-3 text-sm font-semibold tracking-[0.01em] text-terracotta">
                                {{ $featured->person_name }}{{ $featured->person_age ? ', '.$featured->person_age.' tahun' : '' }}
                            </p>
                        @endif

                        @if ($featured->quote)
                            <blockquote class="mt-6 border-l-4 border-gold pl-5 font-display text-lg italic text-navy">
                                “{{ $featured->quote }}”
                            </blockquote>
                        @endif

                        <p class="card-excerpt mt-5 text-base leading-relaxed text-ink-soft">
                            {{ ringkas_isi($featured->excerpt, $featured->content, 260) }}
                        </p>

                        <a href="{{ route('story.show', $featured->slug) }}" class="btn btn-navy mt-8">Baca cerita lengkap</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ================= DAFTAR CERITA ================= --}}
    <section class="{{ $featured ? 'section-soft' : '' }} section-y">
        <div class="container-page">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('story.index') }}" class="chip {{ $activeCategory ? '' : 'is-active' }}">Semua</a>
                @foreach ($categories as $key => $label)
                    <a href="{{ route('story.index', ['kategori' => $key]) }}"
                       class="chip {{ $activeCategory === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <p class="mt-6 meta">
                Menampilkan {{ $stories->count() }} dari {{ $stories->total() }} cerita.
            </p>

            @if ($stories->count())
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($stories as $story)
                        <x-story-card :story="$story" />
                    @endforeach
                </div>
                @if ($stories->hasPages())
                    {{-- Pembungkus hanya dirender bila ada halaman lain; div kosong
                         ber-margin (mt-12) tetap menyumbang 3rem ruang kosong sehingga
                         jeda ke kartu penutup jadi tidak seimbang. --}}
                    <div class="mt-12">{{ $stories->links() }}</div>
                @endif
            @else
                <div class="mt-10 rounded-2xl border border-navy/10 bg-white p-12 text-center">
                    <p class="font-display text-2xl font-semibold text-navy">Belum ada cerita</p>
                    <p class="mt-2 meta">Belum ada cerita pada kategori ini. Silakan pilih kategori lain.</p>
                    <a href="{{ route('story.index') }}" class="btn btn-navy mt-6">Lihat semua cerita</a>
                </div>
            @endif
        </div>
    </section>

    {{-- ================= CTA ================= --}}
    {{--
        Kartu penutup halaman. Jeda atas dan jeda bawahnya dibuat sama besar
        (10rem di ponsel, 12.5rem di layar besar): padding bawah .section-y di
        atasnya ditambah pt-20 / md:pt-25, lalu padding bawah kartu (pb-40 /
        md:pb-50) dipatok sebesar jumlah itu. Ritmenya sama dengan CTA beranda
        dan halaman Tentang Kami.
    --}}
    <section class="pt-20 pb-40 md:pt-25 md:pb-50">
        <div class="container-page">
            <div class="relative isolate overflow-hidden rounded-3xl bg-navy px-8 py-14 text-center md:px-16">
                <div class="hero-pattern absolute inset-0" aria-hidden="true"></div>
                <div class="relative mx-auto max-w-2xl">
                    <h2 class="font-display text-3xl font-semibold text-white md:text-4xl">Punya cerita untuk dibagikan?</h2>
                    <p class="mt-4 text-base text-white/75">
                        Kirimkan kisah inspiratif Anda kepada kami dan menjadi inspirasi bagi perempuan Maluku lainnya.
                    </p>
                    <a href="{{ route('contact') }}" class="btn btn-gold mt-8">Kirim Cerita</a>
                </div>
            </div>
        </div>
    </section>
@endsection
