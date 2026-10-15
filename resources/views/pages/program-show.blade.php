@extends('layouts.app')

@section('title', $program->title)
@section('meta_description', ringkas($program->meta_description ?: $program->excerpt ?: $program->description, 155))

@section('content')
    <x-page-hero
        :eyebrow="$program->category->name ?? 'Program'"
        :title="$program->title"
        :subtitle="$program->excerpt"
        :image="$program->cover_image ? media_url($program->cover_image) : null"
        :breadcrumbs="[
            'Beranda' => route('home'),
            'Program' => route('program.index'),
            Str::limit($program->title, 40) => route('program.show', $program->slug),
        ]" />

    <section class="section-y">
        <div class="container-page grid gap-12 lg:grid-cols-[1.6fr_1fr]">
            {{-- ============ KONTEN UTAMA ============ --}}
            <article>
                <dl class="grid gap-4 rounded-2xl border border-navy/10 bg-white p-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Status</dt>
                        <dd class="mt-1 font-display text-lg font-semibold text-navy">{{ $program->statusLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Wilayah</dt>
                        <dd class="mt-1 font-display text-lg font-semibold text-navy">{{ $program->region->name ?? 'Nasional' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Tahun</dt>
                        <dd class="mt-1 font-display text-lg font-semibold text-navy">{{ $program->fiscal_year ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Dilihat</dt>
                        <dd class="mt-1 font-display text-lg font-semibold text-navy">{{ number_format((int) $program->views, 0, ',', '.') }}x</dd>
                    </div>
                </dl>

                @if ($program->description)
                    <div class="prose-perwama mt-10 text-base">
                        {!! $program->description !!}
                    </div>
                @endif

                @if ($program->goal)
                    <div class="mt-10 rounded-2xl border-l-4 border-gold bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-gold">Tujuan Program</p>
                        <p class="mt-3 text-base leading-relaxed text-ink-soft">{{ $program->goal }}</p>
                    </div>
                @endif

                @php
                    $bullets = fn (?string $text) => collect(preg_split('/\r\n|\r|\n/', (string) $text))
                        ->map(fn ($line) => trim(ltrim($line, "-•* \t")))
                        ->filter();
                @endphp

                @if (filled($program->objectives))
                    <div class="mt-10">
                        <h2 class="section-title text-2xl">Sasaran &amp; Target</h2>
                        <ul class="mt-5 space-y-3">
                            @foreach ($bullets($program->objectives) as $objective)
                                <li class="flex gap-3 text-base leading-relaxed text-ink-soft">
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-gold" aria-hidden="true"></span>
                                    <span>{{ $objective }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (filled($program->achievements))
                    <div class="mt-10">
                        <h2 class="section-title text-2xl">Capaian</h2>
                        <ul class="mt-5 space-y-3">
                            @foreach ($bullets($program->achievements) as $achievement)
                                <li class="flex gap-3 text-base leading-relaxed text-ink-soft">
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green" aria-hidden="true"></span>
                                    <span>{{ $achievement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </article>

            {{-- ============ SIDEBAR ============ --}}
            <aside class="space-y-6">
                @if ($program->target_participants)
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-terracotta">Peserta Sasaran</p>
                        <p class="mt-3 text-base leading-relaxed text-ink-soft">{{ $program->target_participants }}</p>
                    </div>
                @endif

                @if ($program->document)
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Dokumen Program</p>
                        <p class="mt-2 meta">Unduh dokumen resmi program untuk informasi lengkap.</p>
                        <a href="{{ media_url($program->document) }}" target="_blank" rel="noopener" class="btn btn-navy mt-4 w-full">
                            Unduh Dokumen
                        </a>
                    </div>
                @endif

                @if ($others->count())
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Program Lainnya</p>
                        <ul class="mt-4 space-y-4">
                            @foreach ($others as $other)
                                <li>
                                    <a href="{{ route('program.show', $other->slug) }}" class="group block">
                                        <p class="font-display text-sm font-semibold leading-snug text-navy group-hover:text-terracotta">
                                            {{ $other->title }}
                                        </p>
                                        <p class="mt-1 meta">{{ $other->category->name ?? 'Program' }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-2xl bg-navy p-7 text-white">
                    <p class="font-display text-xl font-semibold text-white">Tertarik berkolaborasi?</p>
                    <p class="mt-3 text-base leading-relaxed text-white/75">
                        Kami membuka peluang kerja sama untuk mendukung keberlanjutan program ini.
                    </p>
                    <a href="{{ route('contact') }}" class="btn btn-gold mt-6 w-full">Hubungi Kami</a>
                </div>
            </aside>
        </div>
    </section>

    {{-- ============ KEGIATAN TERKAIT ============ --}}
    @if ($program->activities->count())
        <section class="section-soft section-y">
            <div class="container-page">
                <h2 class="section-title text-2xl md:text-3xl">Kegiatan Terkait</h2>
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($program->activities as $activity)
                        <x-activity-card :activity="$activity" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($program->stories->count())
        <section class="section-y">
            <div class="container-page">
                <h2 class="section-title text-2xl md:text-3xl">Cerita Dampak</h2>
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($program->stories as $story)
                        <x-story-card :story="$story" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

