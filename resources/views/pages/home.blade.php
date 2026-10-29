@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    @php
        /*
         * Slide hero diambil dari gambar yang diunggah khusus pada
         * Pengaturan → Tampilan Website. Bila belum ada, cover galeri dipakai
         * sebagai cadangan. Hanya entri bergambar yang dipakai agar carousel
         * tidak pernah menampilkan slide kosong.
         */
        $website = \App\Models\WebsiteSetting::current();

        $heroSlides = $website->heroImageUrls()
            ->map(fn (string $url): array => [
                'image' => $url,
                'title' => null,
                'url' => null,
            ]);

        if ($heroSlides->isEmpty()) {
            $heroSlides = $galleries
                ->map(fn ($gallery): array => [
                    'image' => media_url($gallery->cover_image),
                    'title' => $gallery->title,
                    'url' => $gallery->slug ? route('gallery.show', $gallery->slug) : null,
                ])
                ->filter(fn (array $slide): bool => filled($slide['image']));
        }

        $heroSlides = $heroSlides->take(6)->values();
    @endphp

    {{-- ================= HERO ================= --}}
    {{--
        Hero beranda memakai gambar hero (Pengaturan → Tampilan Website) sebagai
        latar penuh (full-bleed) dengan carousel fade. Teks diletakkan di atas
        overlay gelap agar tetap kontras. Teks hero dipusatkan agar fokus pada
        pesan utama tanpa elemen tambahan di sisi kanan.
    --}}
    <section class="hero-full relative isolate flex items-center overflow-hidden bg-navy-deep">
        {{-- Latar carousel: hanya entri bergambar yang dipakai. --}}
        <div class="hero-bg" data-carousel data-carousel-autoplay="6000">
            @forelse ($heroSlides as $index => $slide)
                <div class="hero-bg-slide {{ $index === 0 ? 'is-active' : '' }}"
                     data-carousel-slide
                     role="img"
                     aria-label="{{ $slide['title'] ?: 'Dokumentasi kegiatan PERWAMA INA' }}"
                     aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                     style="background-image: url('{{ $slide['image'] }}');"></div>
            @empty
                <div class="hero-bg-slide hero-bg-fallback is-active"
                     data-carousel-slide
                     role="img"
                     aria-label="Dokumentasi kegiatan PERWAMA INA"></div>
            @endforelse

            {{-- Kontrol & indikator harus berada di dalam [data-carousel] agar
                 ditemukan oleh carousel.querySelector() pada app.js. --}}
            @if ($heroSlides->count() > 1)
                {{-- Kontrol carousel naik/turun di sisi kanan. --}}
                <div class="hero-bg-controls">
                    <button type="button" class="hero-bg-nav" data-carousel-prev aria-label="Gambar sebelumnya">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                        </svg>
                    </button>
                    <button type="button" class="hero-bg-nav" data-carousel-next aria-label="Gambar berikutnya">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                {{-- Indikator berada di tengah bawah. --}}
                <div class="hero-bg-dots">
                    @foreach ($heroSlides as $index => $slide)
                        <button type="button"
                                class="hero-bg-dot {{ $index === 0 ? 'is-active' : '' }}"
                                data-carousel-dot="{{ $index }}"
                                aria-label="Tampilkan gambar {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Overlay gradien agar teks tetap terbaca di atas foto. --}}
        <div class="hero-overlay absolute inset-0" aria-hidden="true"></div>

        {{--
            Padding bawah isi hero lebih besar di ponsel (pb-20 = 5rem) daripada
            di layar besar (md:py-16 = 4rem). Sebabnya, indikator carousel
            (.hero-bg-dots) dipatok 2.5rem dari dasar hero dan tombol CTA
            menumpuk selebar layar di ponsel: dengan padding 3rem (py-12) hanya
            tersisa ~8px sehingga titik indikator nyaris menempel pada tombol
            "Jelajahi Program". Dengan pb-20 tombol selalu berada 5rem di atas
            dasar hero, jadi jeda ke titik indikator tetap ±2.1rem (2.5rem
            dikurangi tinggi titik, 0.375rem) tak peduli setinggi apa isinya.
        --}}
        <div class="container-page relative z-20 pt-12 pb-20 md:py-16">
            <div class="mx-auto max-w-4xl text-center">
                <div class="flex items-center justify-center gap-3">
                    <span class="h-[2px] w-8 bg-gold" aria-hidden="true"></span>
                    <p class="section-eyebrow text-gold">{{ $profile->tagline ?: 'Persatuan Wanita Maluku Indonesia' }}</p>
                </div>

                <h1 class="mx-auto mt-6 max-w-4xl font-display text-3xl font-bold leading-[1.15] text-white sm:text-4xl lg:text-[2.75rem]">
                    {{ $profile->slogan ?: 'Perempuan Maluku Bersatu, Berdaya, dan Berkarya untuk Indonesia' }}
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-base leading-[1.7] text-white/90">
                    {{ $profile->short_description ?: 'PERWAMA INA menghimpun, memberdayakan, dan memperkuat peran perempuan Maluku di seluruh Indonesia melalui pendidikan, kesehatan, ekonomi, dan pelestarian budaya.' }}
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ route('join') }}" class="btn btn-gold">
                        Bergabung Bersama Kami
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                    <a href="{{ route('program.index') }}" class="btn btn-outline">Jelajahi Program</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= TENTANG KAMI ================= --}}
    {{--
        Tiga kartu sejajar (Tentang Kami, Visi, Misi) dengan judul bagian
        terpusat: bentuk mengikuti acuan desain, sedangkan warna, tipografi,
        dan komponen tetap memakai tema yang ada (navy, gold, terracotta,
        cream, Poppins).

        Gambar tiap kartu diunggah pengelola lewat Tentang → Profil Organisasi
        (about_card_image, vision_card_image, mission_card_image) — satu tempat
        dengan isian Visi & Misi. Selama gambar belum tersedia, slot foto
        memakai bidang bermerek bawaan situs, sehingga kartu tetap rapi tanpa
        foto acak.

        Panjang deskripsi ketiga kartu dibatasi sama besar lalu dikunci
        jumlah barisnya, sehingga bentuk kartu seragam. Setiap tombol pada
        kartu mengarah ke halaman Tentang Kami.
    --}}
    <section class="section-y">
        <div class="container-page">
            <div class="mx-auto max-w-3xl text-center" data-reveal>
                <p class="section-eyebrow">Tentang kami</p>
                <h2 class="section-title mt-4 text-3xl md:text-4xl">{{ $profile->name }}</h2>
            </div>

            @php
                /*
                 * Ringkasan untuk tiga kartu beranda.
                 *
                 * Semua kartu memakai batas karakter yang sama agar panjang teks
                 * seimbang, lalu kelas .card-text-clamp mengunci jumlah baris
                 * sehingga tinggi blok deskripsi tetap seragam.
                 *
                 * Bagian hero sudah memakai short_description, sehingga deskripsi
                 * profil yang dipotong dipakai agar tidak ada kalimat yang
                 * terulang di halaman yang sama.
                 */
                $batasDeskripsi = 200;

                $ringkasanTentang = $profile->description
                    ? ringkas_isi(null, $profile->description, $batasDeskripsi)
                    : 'PERWAMA INA adalah wadah kebersamaan perempuan Maluku di seluruh Indonesia. Kami bergerak di bidang pemberdayaan perempuan, pendidikan, kesehatan, sosial, ekonomi kreatif, dan pelestarian budaya Maluku.';

                $sumberVisi = $profile->vision
                    ?: 'Menjadi organisasi perempuan Maluku yang mandiri, berdaya, dan berkontribusi nyata bagi Indonesia.';

                $ringkasanVisi = ringkas_isi(null, $sumberVisi, $batasDeskripsi);

                $misiProfil = $profile->mission ?? [];

                $ringkasanMisi = count($misiProfil)
                    ? ringkas_isi(null, implode(' ', $misiProfil), $batasDeskripsi)
                    : 'Misi organisasi belum diisi melalui panel admin.';
            @endphp

            <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Kartu 1 — tentang kami --}}
                <article class="card flex h-full flex-col text-center" data-reveal>
                    <x-content-media :image="media_url($profile->about_card_image)" :alt="$profile->name" />

                    <div class="flex flex-1 flex-col p-7">
                        <h3 class="font-display text-xl font-semibold text-navy">Tentang Kami</h3>

                        <p class="card-text-clamp mt-4 text-base leading-relaxed text-ink-soft">
                            {{ $ringkasanTentang }}
                        </p>

                        <div class="mt-auto pt-7">
                            <a href="{{ route('about') }}" class="btn btn-gold">
                                Lihat Selengkapnya <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </article>

                {{-- Kartu 2 — visi --}}
                <article class="card flex h-full flex-col text-center" data-reveal>
                    <x-content-media :image="media_url($profile->vision_card_image)" alt="Visi" />

                    <div class="flex flex-1 flex-col p-7">
                        <h3 class="font-display text-xl font-semibold text-navy">Visi</h3>

                        <p class="card-text-clamp mt-4 text-base leading-relaxed text-ink-soft">
                            {{ $ringkasanVisi }}
                        </p>

                        <div class="mt-auto pt-7">
                            <a href="{{ route('about') }}" class="btn btn-gold">
                                Lihat Selengkapnya <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </article>

                {{-- Kartu 3 — misi --}}
                <article class="card flex h-full flex-col text-center" data-reveal>
                    <x-content-media :image="media_url($profile->mission_card_image)" alt="Misi" />

                    <div class="flex flex-1 flex-col p-7">
                        <h3 class="font-display text-xl font-semibold text-navy">Misi</h3>

                        <p class="card-text-clamp mt-4 text-base leading-relaxed text-ink-soft">
                            {{ $ringkasanMisi }}
                        </p>

                        <div class="mt-auto pt-7">
                            <a href="{{ route('about') }}" class="btn btn-gold">
                                Lihat Selengkapnya <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- ================= SEBARAN WILAYAH ================= --}}
    @if ($provinces->count())
        <section class="section-y bg-navy text-white">
            <div class="container-page grid gap-16 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
                <div data-reveal>
                    <p class="section-eyebrow text-gold">Wilayah</p>
                    <h2 class="mt-4 font-display text-3xl leading-[1.2] text-white md:text-4xl">
                        {{ $website->region_section_title ?: 'Jejaring perempuan Maluku di seluruh Nusantara' }}
                    </h2>
                    <p class="mt-6 text-lg leading-relaxed text-white/85">
                        {{ $website->region_section_description ?: 'Dari Maluku hingga perantauan, PERWAMA INA hadir melalui wilayah, cabang, dan komunitas anggota yang tersebar di berbagai provinsi.' }}
                    </p>

                    <div class="mt-10 grid grid-cols-2 gap-8 border-t border-white/15 pt-10">
                        <div>
                            <p class="font-display text-3xl text-gold">{{ $provinces->count() }}</p>
                            <p class="mt-2 text-sm text-white/80">Provinsi</p>
                        </div>
                        <div>
                            <p class="font-display text-3xl text-gold">{{ $regionTotal }}</p>
                            <p class="mt-2 text-sm text-white/80">Wilayah kerja</p>
                        </div>
                    </div>

                    <a href="{{ route('region.index') }}" class="btn btn-gold mt-10">Lihat peta sebaran</a>
                </div>

                <div class="grid gap-4 sm:grid-cols-2" data-reveal>
                    @foreach ($provinces as $province)
                        <a href="{{ route('region.show', $province->slug) }}"
                           class="group flex items-start justify-between gap-4 rounded-xl border border-white/12 bg-white/5 px-6 py-6 transition hover:border-gold/60 hover:bg-white/10">
                            <span>
                                <span class="block text-base font-semibold text-white">{{ $province->name }}</span>
                                <span class="mt-1.5 block text-sm text-white/75">
                                    {{ $province->island ?: 'Wilayah' }}@if ($province->organizations_count) &middot; {{ $province->organizations_count }} organisasi @endif
                                </span>
                            </span>
                            <span class="mt-1 text-gold opacity-0 transition group-hover:opacity-100" aria-hidden="true">&rarr;</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= BERITA ================= --}}
    {{--
        Tata letak dua kolom: satu berita unggulan (foto besar, kategori,
        tanggal, judul besar, dan ringkasan) di kolom kiri, lalu tiga berita
        berikutnya sebagai kartu ringkas berfoto persegi di kolom kanan.
        Seluruh warna, tipografi, dan bidang gambar memakai gaya kartu yang
        sudah dipakai di halaman lain (.card, .card-kicker, .card-title-*).
    --}}
    @if ($newsItems->count())
        <section class="section-y">
            <div class="container-page">
                <div class="flex flex-wrap items-end justify-between gap-8" data-reveal>
                    <div class="max-w-2xl">
                        <p class="section-eyebrow">Berita</p>
                        <h2 class="section-title mt-4 text-3xl md:text-4xl">Kabar terbaru</h2>
                    </div>
                    <a href="{{ route('news.index') }}" class="btn btn-quiet">
                        Semua berita
                    </a>
                </div>

                @php
                    $kabarUtama = $newsItems->first();
                @endphp
                <div class="mt-14 grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:gap-16">
                    <article class="group" data-reveal>
                        <a href="{{ route('news.show', $kabarUtama->slug) }}" class="block overflow-hidden rounded-2xl" tabindex="-1" aria-hidden="true">
                            <x-content-media :image="media_url($kabarUtama->cover_image)" :alt="$kabarUtama->title" ratio="wide" />
                        </a>

                        <p class="mt-7 flex flex-wrap items-center gap-x-4 gap-y-2">
                            <span class="card-kicker text-terracotta">{{ $kabarUtama->categoryLabel() }}</span>
                            <time class="meta" datetime="{{ optional($kabarUtama->published_at)->toDateString() }}">{{ tanggal_id($kabarUtama->published_at, 'd F Y') }}</time>
                        </p>

                        <h3 class="card-title-wide mt-4 font-display text-2xl leading-snug text-navy md:text-3xl">
                            <a href="{{ route('news.show', $kabarUtama->slug) }}" class="transition hover:text-terracotta">{{ $kabarUtama->title }}</a>
                        </h3>

                        <p class="card-excerpt mt-5 max-w-prose text-lg leading-relaxed text-ink-soft">
                            {{ ringkas_isi($kabarUtama->excerpt, $kabarUtama->content, 240) }}
                        </p>

                        <a href="{{ route('news.show', $kabarUtama->slug) }}" class="mt-7 inline-flex items-center gap-2 text-base font-semibold text-terracotta">
                            Baca berita <span aria-hidden="true">&rarr;</span>
                        </a>
                    </article>

                    {{-- Tiga berita berikutnya sebagai kartu ringkas berfoto persegi. --}}
                    <div class="flex flex-col gap-7">
                        @foreach ($newsItems->skip(1) as $item)
                            <article class="card group flex gap-5 p-5" data-reveal>
                                <a href="{{ route('news.show', $item->slug) }}" class="block w-28 shrink-0 overflow-hidden rounded-xl sm:w-32" tabindex="-1" aria-hidden="true">
                                    <x-content-media :image="media_url($item->cover_image)" :alt="$item->title" ratio="square" />
                                </a>

                                <div class="flex min-w-0 flex-1 flex-col justify-center">
                                    <p class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                        <span class="card-kicker text-terracotta">{{ $item->categoryLabel() }}</span>
                                        <time class="meta" datetime="{{ optional($item->published_at)->toDateString() }}">{{ tanggal_id($item->published_at, 'd F Y') }}</time>
                                    </p>
                                    <h3 class="card-title-wide mt-3 font-display text-lg leading-snug text-navy md:text-xl">
                                        <a href="{{ route('news.show', $item->slug) }}" class="transition hover:text-terracotta">{{ $item->title }}</a>
                                    </h3>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Mitra kerja sama: logo, jenis, dan tautan dari panel Partners. --}}
    @include('partials.partners', ['partners' => $partners, 'latarMitra' => 'section-soft'])

    {{-- ================= CTA BERGABUNG ================= --}}
    {{--
        Kartu penutup beranda. Jeda atas dan jeda bawahnya dibuat sama besar
        supaya kartu tidak tampak menempel pada bagian di atasnya lalu melayang
        jauh dari pita footer.

        Jeda atas = padding bawah bagian sebelumnya (.section-y: 5rem di ponsel,
        6.25rem di layar besar) + padding atas bagian ini (pt-20 / md:pt-25,
        nilainya mengikuti .section-y) = 10rem / 12.5rem. Angka itu persis ritme
        antar bagian lain di beranda, sehingga kartu ini duduk pada irama yang
        sama dengan bagian lain. Padding bawah kartu (pb-40 = 10rem, md:pb-50 =
        12.5rem) dipatok sebesar jumlah tadi, jadi jarak ke bagian di atas sama
        dengan jarak ke footer.
    --}}
    <section class="pt-20 pb-40 md:pt-25 md:pb-50">
        <div class="container-page">
            <div class="relative isolate overflow-hidden rounded-3xl bg-navy-deep px-8 py-16 md:px-16 md:py-24">
                <div class="hero-pattern absolute inset-0" aria-hidden="true"></div>
                <div class="pattern-dots absolute inset-0 opacity-40" aria-hidden="true"></div>

                <div class="relative mx-auto max-w-3xl text-center">
                    <p class="section-eyebrow text-gold">Bergabung</p>
                    <h2 class="mt-5 font-display text-3xl leading-[1.2] text-white md:text-4xl">
                        Mari berjalan bersama perempuan Maluku
                    </h2>
                    <p class="mt-6 text-lg leading-relaxed text-white/85">
                        Jadilah bagian dari gerakan pemberdayaan perempuan Maluku — sebagai anggota, relawan,
                        maupun mitra kolaborasi.
                    </p>
                    <div class="mt-10 flex flex-wrap justify-center gap-4">
                        <a href="{{ route('join') }}" class="btn btn-gold">Daftar sekarang</a>
                        <a href="{{ route('contact') }}" class="btn btn-outline">Hubungi kami</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection