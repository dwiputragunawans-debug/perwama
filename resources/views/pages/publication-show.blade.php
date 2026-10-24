@extends('layouts.app')

@section('title', $publication->title)

@section('content')
    <x-page-hero
        :eyebrow="$publication->categoryLabel()"
        :title="$publication->title"
        :subtitle="$publication->description ? ringkas_isi($publication->description, null, 230) : null"
        :image="$publication->cover_image ? media_url($publication->cover_image) : null"
        :breadcrumbs="[
            'Beranda' => route('home'),
            'Publikasi' => route('publication.index'),
            Str::limit($publication->title, 40) => route('publication.show', $publication->slug),
        ]" />

    <section class="section-y">
        <div class="container-page grid gap-12 lg:grid-cols-[1.6fr_1fr]">
            <article>
                @if ($publication->cover_image)
                    <img src="{{ media_url($publication->cover_image) }}" alt="{{ $publication->title }}"
                         class="w-full rounded-2xl object-cover" loading="lazy">
                @endif

                @if ($publication->description)
                    <div class="prose-perwama mt-10 text-base">
                        {!! nl2br(e($publication->description)) !!}
                    </div>
                @endif

                @if ($publication->tags->count())
                    <div class="mt-10 flex flex-wrap items-center gap-2">
                        <span class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Tag:</span>
                        @foreach ($publication->tags as $tag)
                            <span class="chip">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </article>

            <aside class="space-y-6">
                <div class="rounded-2xl border border-navy/10 bg-white p-6">
                    <p class="text-sm font-semibold tracking-[0.01em] text-terracotta">Informasi Dokumen</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-soft">Kategori</dt>
                            <dd class="font-medium text-navy">{{ $publication->categoryLabel() }}</dd>
                        </div>
                        @if ($publication->year)
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink-soft">Tahun</dt>
                                <dd class="font-medium text-navy">{{ $publication->year }}</dd>
                            </div>
                        @endif
                        @if ($publication->page_count)
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink-soft">Jumlah halaman</dt>
                                <dd class="font-medium text-navy">{{ $publication->page_count }}</dd>
                            </div>
                        @endif
                        @if ($publication->file_size)
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink-soft">Ukuran berkas</dt>
                                <dd class="font-medium text-navy">{{ $publication->fileSizeLabel() }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-soft">Diunduh</dt>
                            <dd class="font-medium text-navy">{{ number_format((int) $publication->download_count, 0, ',', '.') }}x</dd>
                        </div>
                        @if ($publication->program)
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink-soft">Program</dt>
                                <dd class="text-right font-medium text-navy">
                                    <a href="{{ route('program.show', $publication->program->slug) }}" class="hover:text-terracotta">
                                        {{ $publication->program->title }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>

                    @if ($publication->file)
                        <a href="{{ media_url($publication->file) }}" target="_blank" rel="noopener" class="btn btn-navy mt-6 w-full">
                            Unduh Dokumen
                        </a>
                    @endif
                </div>

                @if ($others->count())
                    <div class="rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Publikasi Lainnya</p>
                        <ul class="mt-4 space-y-4">
                            @foreach ($others as $other)
                                <li>
                                    <a href="{{ route('publication.show', $other->slug) }}" class="group block">
                                        <p class="font-display text-sm font-semibold leading-snug text-navy group-hover:text-terracotta">{{ $other->title }}</p>
                                        <p class="mt-1 meta">{{ $other->categoryLabel() }}</p>
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
