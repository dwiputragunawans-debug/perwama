{{--
    Mitra kerja sama yang dikelola lewat panel: Mitra.

    Hanya mitra aktif yang tampil. Setiap kartu memuat logo (bila diunggah),
    nama, jenis, ringkasan, dan tautan situs. Partial dipakai di beranda dan
    halaman Tentang Kami agar data mitra tidak lagi hanya tersimpan di panel.
--}}
@php
    $daftarMitra = collect($partners ?? [])->filter()->values();
    $judulMitra = $judulMitra ?? 'Bermitra untuk dampak yang lebih luas';
    $eyebrowMitra = $eyebrowMitra ?? 'Jejaring & Mitra';
    $latarMitra = $latarMitra ?? '';
@endphp

@if ($daftarMitra->isNotEmpty())
    <section class="section-y {{ $latarMitra }}" aria-labelledby="mitra-kerja-sama">
        <div class="container-page">
            <div class="mx-auto max-w-3xl text-center" data-reveal>
                <p class="section-eyebrow">{{ $eyebrowMitra }}</p>
                <h2 id="mitra-kerja-sama" class="section-title mt-4 text-3xl md:text-4xl">{{ $judulMitra }}</h2>
                <p class="mt-5 text-base leading-relaxed text-ink-soft">
                    PERWAMA INA berjalan bersama lembaga, komunitas, dan mitra pembangunan
                    yang mendukung pemberdayaan perempuan Maluku.
                </p>
            </div>

            <ul class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($daftarMitra as $mitra)
                    @php
                        $logoMitra = media_url($mitra->logo);
                        $tautanMitra = $mitra->website;
                    @endphp
                    <li class="flex h-full flex-col rounded-2xl border border-navy/10 bg-white p-6" data-reveal>
                        <div class="flex h-20 items-center">
                            @if ($logoMitra)
                                <img src="{{ $logoMitra }}" alt="Logo {{ $mitra->name }}"
                                     class="max-h-16 w-auto max-w-[12rem] object-contain" loading="lazy">
                            @else
                                <span class="font-display text-lg font-semibold text-navy">{{ $mitra->name }}</span>
                            @endif
                        </div>

                        <div class="mt-5 flex flex-1 flex-col">
                            @if ($mitra->type)
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-terracotta">{{ $mitra->typeLabel() }}</span>
                            @endif
                            <h3 class="mt-2 font-display text-lg font-semibold text-navy">{{ $mitra->name }}</h3>
                            @if ($mitra->description)
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-ink-soft">
                                    {{ Str::limit(strip_tags($mitra->description), 160) }}
                                </p>
                            @endif

                            @if ($tautanMitra)
                                <a href="{{ $tautanMitra }}" target="_blank" rel="noopener noreferrer"
                                   class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-terracotta hover:underline">
                                    Kunjungi situs
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
