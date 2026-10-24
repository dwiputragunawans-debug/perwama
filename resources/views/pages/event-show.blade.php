@extends('layouts.app')

@section('title', $event->title)

@section('content')
    <x-page-hero
        :eyebrow="'Agenda — '.$event->statusLabel()"
        :title="$event->title"
        :subtitle="$event->location"
        :image="$event->poster ? media_url($event->poster) : null"
        :breadcrumbs="[
            'Beranda' => route('home'),
            'Kegiatan' => route('activity.index', ['tab' => 'agenda']),
            Str::limit($event->title, 40) => route('event.show', $event->slug),
        ]" />

    <section class="section-y">
        <div class="container-page grid gap-12 lg:grid-cols-[1.6fr_1fr]">
            <article>
                <div class="grid gap-4 rounded-2xl border border-navy/10 bg-white p-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Tanggal Mulai</p>
                        <p class="mt-1 font-display text-base font-semibold text-navy">{{ tanggal_id($event->start_date, 'd F Y') ?: '—' }}</p>
                        @if ($event->start_time)
                            <p class="meta">{{ Str::substr($event->start_time, 0, 5) }} WIT</p>
                        @endif
                    </div>
                    @if ($event->end_date)
                        <div>
                            <p class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Tanggal Selesai</p>
                            <p class="mt-1 font-display text-base font-semibold text-navy">{{ tanggal_id($event->end_date, 'd F Y') }}</p>
                            @if ($event->end_time)
                                <p class="meta">{{ Str::substr($event->end_time, 0, 5) }} WIT</p>
                            @endif
                        </div>
                    @endif
                    <div>
                        <p class="text-xs font-semibold tracking-[0.01em] text-ink-soft">Status</p>
                        <p class="mt-1 font-display text-base font-semibold text-navy">{{ $event->statusLabel() }}</p>
                    </div>
                </div>

                @if ($event->description)
                    <div class="prose-perwama mt-10 text-base">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                @endif

                @if ($event->activities->count())
                    <div class="mt-12">
                        <h2 class="section-title text-2xl">Kegiatan dalam Agenda Ini</h2>
                        <div class="mt-8 grid gap-6 sm:grid-cols-2">
                            @foreach ($event->activities as $activity)
                                <x-activity-card :activity="$activity" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>

            <aside class="space-y-6">
                @if ($event->poster)
                    <div class="overflow-hidden rounded-2xl border border-navy/10 bg-white">
                        <img src="{{ media_url($event->poster) }}" alt="{{ $event->title }}" class="w-full object-cover" loading="lazy">
                    </div>
                @endif

                <div class="rounded-2xl border border-navy/10 bg-white p-6">
                    <p class="text-sm font-semibold tracking-[0.01em] text-terracotta">Detail Acara</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        @if ($event->location)
                            <div>
                                <dt class="text-ink-soft">Lokasi</dt>
                                <dd class="font-medium text-navy">{{ $event->location }}</dd>
                            </div>
                        @endif
                        @if ($event->speaker)
                            <div>
                                <dt class="text-ink-soft">Narasumber</dt>
                                <dd class="font-medium text-navy">{{ $event->speaker }}</dd>
                            </div>
                        @endif
                        @if ($event->region)
                            <div>
                                <dt class="text-ink-soft">Wilayah</dt>
                                <dd class="font-medium text-navy">{{ $event->region->name }}</dd>
                            </div>
                        @endif
                        @if ($event->program)
                            <div>
                                <dt class="text-ink-soft">Program</dt>
                                <dd>
                                    <a href="{{ route('program.show', $event->program->slug) }}" class="font-medium text-navy hover:text-terracotta">
                                        {{ $event->program->title }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                @if ($agendas->count())
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Agenda Lainnya</p>
                        <ul class="mt-4 space-y-4">
                            @foreach ($agendas as $other)
                                <li>
                                    <a href="{{ route('event.show', $other->slug) }}" class="group block">
                                        <p class="font-display text-sm font-semibold leading-snug text-navy group-hover:text-terracotta">{{ $other->title }}</p>
                                        <p class="mt-1 meta">{{ tanggal_id($other->start_date, 'd M Y') }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </section>
    @include('partials.media-attachments', ['media' => $media])
@endsection

