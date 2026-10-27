@extends('layouts.app')

@section('title', 'Wilayah')

@section('content')
    <x-page-hero
        eyebrow="Wilayah"
        title="Jangkauan PERWAMA INA di Indonesia"
        subtitle="Dari Maluku hingga penjuru Nusantara — jaringan wilayah, organisasi, dan anggota yang terus tumbuh."
        :breadcrumbs="['Beranda' => route('home'), 'Wilayah' => route('region.index')]" />

    {{-- ================= STATISTIK ================= --}}
    <section class="border-b border-navy/10 bg-white py-10">
        <div class="container-page grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                'region' => 'Wilayah',
                'province' => 'Provinsi',
                'organization' => 'Organisasi',
                'member' => 'Anggota',
            ] as $key => $label)
                <div class="text-center">
                    <p class="font-display text-4xl font-semibold text-navy">{{ number_format($totals[$key] ?? 0, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs font-semibold tracking-[0.2em] text-ink-soft">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= WILAYAH PER PULAU ================= --}}
    <section class="section-y">
        <div class="container-page space-y-16">
            @forelse ($regionsByIsland as $island => $regions)
                <div>
                    <div class="flex flex-wrap items-end justify-between gap-3" data-reveal>
                        <div>
                            <p class="section-eyebrow">Wilayah</p>
                            <h2 class="section-title mt-2 text-2xl md:text-3xl">{{ $island }}</h2>
                        </div>
                        <p class="meta">{{ $regions->count() }} provinsi</p>
                    </div>

                    <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($regions as $region)
                            <article class="card group flex h-full flex-col" data-reveal>
                                <a href="{{ route('region.show', $region->slug) }}" class="block overflow-hidden">
                                    @if ($region->cover_image)
                                        <img src="{{ media_url($region->cover_image) }}" alt="{{ $region->name }}"
                                             class="card-media transition duration-500 group-hover:scale-105" loading="lazy">
                                    @else
                                        <div class="hero-pattern flex h-40 items-center justify-center bg-navy">
                                            <span class="font-display text-3xl font-semibold text-white/80">{{ Str::substr($region->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </a>

                                <div class="flex flex-1 flex-col p-6">
                                    <h3 class="card-title font-display text-xl font-semibold text-navy">
                                        <a href="{{ route('region.show', $region->slug) }}" class="transition hover:text-terracotta">{{ $region->name }}</a>
                                    </h3>

                                    @if ($region->description)
                                        <p class="card-excerpt mt-3 text-base leading-relaxed text-ink-soft">{{ ringkas_isi($region->description, null, 180) }}</p>
                                    @endif

                                    <p class="mt-5 flex flex-wrap gap-x-4 gap-y-1 meta">
                                        <span><strong class="text-navy">{{ $region->dpw_count }}</strong> organisasi</span>
                                        <span><strong class="text-navy">{{ $region->pc_count }}</strong> cabang</span>
                                    </p>

                                    <a href="{{ route('region.show', $region->slug) }}"
                                       class="mt-auto pt-5 text-sm font-semibold tracking-[0.01em] text-terracotta transition hover:text-navy">
                                        Lihat wilayah &rarr;
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-navy/10 bg-white p-12 text-center">
                    <p class="font-display text-2xl font-semibold text-navy">Data wilayah belum tersedia</p>
                    <p class="mt-2 meta">Silakan kembali lagi nanti untuk melihat sebaran wilayah kami.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
