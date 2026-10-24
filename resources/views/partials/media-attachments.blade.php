{{--
    Daftar lampiran berkas milik sebuah konten (tabel `media`).

    Berkas diunggah lewat panel admin: Pengaturan → Media, lalu pilih jenis
    konten dan kontennya. Partial ini dipakai pada halaman detail berita,
    program, cerita, kegiatan, agenda, publikasi, dan wilayah. Bila konten
    belum memiliki lampiran, tidak ada markup yang dirender.
--}}
@php
    $attachments = collect($media ?? [])
        ->filter(fn ($item): bool => filled($item->url()))
        ->values();
    $judulLampiran = $judulLampiran ?? 'Lampiran & Berkas';
@endphp

@if ($attachments->isNotEmpty())
    <section class="pb-16 md:pb-20" aria-labelledby="lampiran-berkas">
        <div class="container-page">
            <div class="mx-auto max-w-4xl">
                <h2 id="lampiran-berkas" class="section-title text-2xl">{{ $judulLampiran }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                    Dokumen pendukung yang dapat diunduh atau dibuka pada tab baru.
                </p>

                <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ($attachments as $item)
                        @php
                            $berkasUrl = $item->url();
                            $ekstensi = strtoupper(pathinfo((string) $item->path, PATHINFO_EXTENSION)) ?: 'FILE';
                        @endphp
                        <li>
                            <a href="{{ $berkasUrl }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="group flex h-full items-start gap-4 rounded-2xl border border-navy/10 bg-white p-4 transition hover:-translate-y-0.5 hover:border-gold/60 hover:shadow-md">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-navy/5 font-display text-xs font-semibold tracking-wide text-navy">
                                    {{ $ekstensi }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-semibold text-navy group-hover:text-terracotta">
                                        {{ $item->displayName() }}
                                    </span>
                                    <span class="mt-1 block text-sm text-ink-soft">
                                        {{ $item->sizeLabel() ?: 'Berkas' }}
                                        @if ($item->alt)
                                            · {{ $item->alt }}
                                        @endif
                                    </span>
                                </span>
                                <svg class="mt-1 h-4 w-4 shrink-0 text-ink-soft transition group-hover:text-terracotta" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H9m8 0v8"/>
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
@endif
