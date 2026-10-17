@extends('layouts.app')

@section('title', 'Tentang Kami')

@section('content')
    @php
        /*
         * Halaman Tentang Kami memakai hero bersama <x-page-hero> — sama
         * seperti halaman lain kecuali beranda — lalu sekilas organisasi yang
         * ditutup pita statistik, visi & misi, bidang pengabdian, sejarah, dan
         * struktur pengurus bertab. Warna, tipografi, dan komponen tetap
         * memakai tema situs (navy, gold, terracotta, hijau, cream, Poppins).
         */
        $mission = $profile->mission ?? [];

        // Foto hero: unggahan khusus (Tentang → Profil Organisasi); bila belum
        // ada, cover galeri pertama dipakai sebagai cadangan.
        $heroImage = media_url($profile->about_card_image ?: $heroCover);

        // Label tingkat kepengurusan pada tab struktur pengurus.
        $levels = [
            'dpp' => 'Pengurus Inti DPP',
            'dpw' => 'Pengurus DPW',
            'pc' => 'Pengurus Cabang (PC)',
        ];
    @endphp

    {{-- ================= HERO ================= --}}
    {{--
        Hero memakai komponen bersama <x-page-hero>, sama seperti halaman lain
        (kecuali beranda): eyebrow bergaris emas, judul, ringkasan profil, lalu
        breadcrumb "Beranda / Tentang Kami". Foto profil organisasi — dengan
        cover galeri pertama sebagai cadangan — dipakai sebagai latar tipis.
    --}}
    <x-page-hero
        eyebrow="Profil Organisasi"
        :title="$profile->name"
        :subtitle="$profile->short_description ?: ringkas($profile->description, 220)"
        :image="$heroImage"
        :breadcrumbs="['Beranda' => route('home'), 'Tentang Kami' => route('about')]" />

    {{-- ================= PROFIL ================= --}}
    <section class="section-y">
        {{--
            `min-w-0` pada SETIAP anak kisi wajib: tanpa itu trek `1fr`
            memakai lebar min-content anaknya (kartu sekretariat dengan surel
            yang tak bisa dipatahkan ±265px, dan ±336px bila pengguna
            memperbesar ukuran teks sistem), sehingga trek melebar melewati
            padding kanan `.container-page` dan seluruh narasi ikut terpotong.
            Dengan `min-width: 0` trek selalu selebar kontainer dan teks
            membungkus di dalamnya, berapa pun ukuran hurufnya.
        --}}
        <div class="container-page grid gap-14 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="min-w-0" data-reveal>
                <p class="section-eyebrow">Sekilas Organisasi</p>
                <h2 class="section-title mt-3 text-3xl md:text-4xl">{{ $profile->abbreviation ?: 'PERWAMA INA' }}</h2>
                <div class="gold-rule mt-6"></div>

                {{--
                    Tagline dan tahun berdiri tampil sebagai satu baris
                    keterangan di bawah judul bagian — menggantikan lencana
                    "Tahun Berdiri" pada hero dua kolom yang lama.
                --}}
                @if ($profile->tagline || $profile->founded_year)
                    <p class="meta mt-4">
                        {{ $profile->tagline }}@if ($profile->tagline && $profile->founded_year) · @endif@if ($profile->founded_year) Berdiri sejak {{ $profile->founded_year }}@endif
                    </p>
                @endif

                <div class="prose-perwama mt-6 text-base">
                    {!! $profile->description ?: '<p>'.$profile->short_description.'</p>' !!}
                </div>
            </div>

            <aside class="min-w-0 space-y-6" data-reveal>
                @php
                    /*
                     * Empat angka pertama sudah tampil pada pita statistik di
                     * dasar bagian ini; sisa angka ditampilkan di sini agar
                     * seluruh data tetap terlihat tanpa mengulanginya.
                     */
                    $capaianLain = $statistics->skip(4);
                @endphp

                @if ($profile->address)
                    <div class="rounded-2xl border border-navy/10 bg-white p-7">
                        <p class="section-eyebrow">Sekretariat</p>
                        <p class="mt-3 text-base leading-relaxed text-ink-soft">
                            {{ $profile->address }}@if ($profile->city), {{ $profile->city }}@endif
                            @if ($profile->province) {{ $profile->province }}@endif
                            @if ($profile->postal_code) {{ $profile->postal_code }}@endif
                        </p>

                        <dl class="mt-5 space-y-2 text-base">
                            @if ($profile->email)
                                <div class="flex flex-col gap-1 sm:flex-row sm:gap-3">
                                    <dt class="meta sm:w-24 sm:shrink-0">Email</dt>
                                    <dd class="min-w-0 wrap-anywhere">
                                        <a href="mailto:{{ $profile->email }}" class="font-semibold break-words text-terracotta hover:underline">{{ $profile->email }}</a>
                                    </dd>
                                </div>
                            @endif
                            @if ($profile->phone)
                                <div class="flex flex-col gap-1 sm:flex-row sm:gap-3">
                                    <dt class="meta sm:w-24 sm:shrink-0">Telepon</dt>
                                    <dd class="min-w-0 wrap-anywhere">{{ $profile->phone }}</dd>
                                </div>
                            @endif
                            @if ($profile->whatsapp)
                                <div class="flex flex-col gap-1 sm:flex-row sm:gap-3">
                                    <dt class="meta sm:w-24 sm:shrink-0">WhatsApp</dt>
                                    <dd class="min-w-0 wrap-anywhere">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->whatsapp) }}" target="_blank" rel="noopener" class="font-semibold text-terracotta hover:underline">{{ $profile->whatsapp }}</a>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                @if ($capaianLain->isNotEmpty())
                    <div class="rounded-2xl border border-navy/10 bg-white p-7">
                        <p class="section-eyebrow">Capaian lainnya</p>
                        <div class="mt-5 grid grid-cols-2 gap-5">
                            @foreach ($capaianLain as $stat)
                                <div>
                                    <p class="font-display text-2xl font-semibold text-navy">
                                        {{ $stat->value }}<span class="text-base text-gold">{{ $stat->suffix }}</span>
                                    </p>
                                    <p class="mt-1 text-sm text-ink-soft">{{ $stat->label }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>

            {{--
                Pita statistik: empat angka utama organisasi pada satu kartu
                putih, ditempatkan di dasar bagian Sekilas Organisasi dan
                melintang penuh di bawah narasi profil serta data sekretariat.
            --}}
            @if ($statistics->isNotEmpty())
                <div class="stat-band min-w-0 lg:col-span-2" data-reveal>
                    @foreach ($statistics->take(4) as $stat)
                        <div class="stat-item">
                            <p class="stat-value">
                                {{ $stat->value }}<span class="text-gold">{{ $stat->suffix }}</span>
                            </p>
                            <p class="stat-label">{{ $stat->label }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ================= VISI & MISI ================= --}}
    {{--
        Visi ditampilkan sebagai kutipan pada kartu navy (mengikuti acuan
        tampilan), sedangkan misi tampil sebagai daftar bernomor pada kartu
        putih agar mudah dibaca satu per satu.
    --}}
    @if (filled($profile->vision) || count($mission))
        <section id="visi-misi" class="section-soft section-y">
            <div class="container-page">
                <div class="mx-auto max-w-3xl text-center" data-reveal>
                    <p class="section-eyebrow">Pilar Utama</p>
                    <h2 class="section-title mt-4 text-3xl md:text-4xl">Visi &amp; Misi Organisasi</h2>
                    <p class="mt-4 text-base leading-relaxed text-ink-soft">
                        Komitmen bersama untuk melangkah dan berdampak nyata.
                    </p>
                </div>

                <div class="mt-14 grid gap-8 lg:grid-cols-2">
                    @if (filled($profile->vision))
                        <div class="relative isolate overflow-hidden rounded-3xl bg-navy p-8 md:p-10" data-reveal>
                            <div class="hero-pattern absolute inset-0 opacity-60" aria-hidden="true"></div>

                            <div class="relative flex h-full flex-col">
                                <span class="inline-flex w-fit rounded-full bg-gold px-4 py-1.5 text-sm font-semibold text-navy-deep">Visi Utama</span>

                                <p class="mt-6 font-display text-xl leading-snug text-white md:text-2xl">
                                    <span class="text-gold" aria-hidden="true">“</span>{{ $profile->vision }}<span class="text-gold" aria-hidden="true">”</span>
                                </p>

                                @if ($profile->slogan)
                                    <p class="mt-8 border-t border-white/15 pt-5 text-sm italic text-white/70">
                                        Landasan {{ $profile->abbreviation ?: $profile->name }}: {{ $profile->slogan }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (count($mission))
                        <div class="card p-8 md:p-10" data-reveal>
                            <h3 class="font-display text-xl font-semibold text-navy">Misi Strategi Organisasi</h3>

                            <ol class="mt-6 space-y-5">
                                @foreach ($mission as $index => $item)
                                    <li class="flex gap-4">
                                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-gold/60 font-display text-sm font-semibold text-navy">{{ $index + 1 }}</span>
                                        <span class="text-base leading-relaxed text-ink-soft">{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ================= BIDANG PENGABDIAN ================= --}}
    {{--
        Bidang pengabdian diambil dari kategori program (Program → Kategori
        Program), sehingga menambah atau mengganti kategori otomatis mengubah
        bagian ini. Warna kepala kartu memakai warna kategori yang sama dengan
        penyaring pada halaman Program.
    --}}
    @if ($focusAreas->isNotEmpty())
        <section class="section-y">
            <div class="container-page">
                <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-end" data-reveal>
                    <div>
                        <p class="section-eyebrow">Fokus Gerakan</p>
                        <h2 class="section-title mt-3 text-3xl md:text-4xl">Bidang Pengabdian Utama</h2>
                    </div>
                    <p class="text-base leading-relaxed text-ink-soft">
                        Program kami dirancang secara terstruktur untuk menjangkau berbagai lapisan kebutuhan anggota dan masyarakat.
                    </p>
                </div>

                <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($focusAreas as $focus)
                        <a href="{{ route('program.index', ['kategori' => $focus->slug]) }}" class="card group flex flex-col" data-reveal>
                            <div class="p-7" style="background-color: {{ $focus->color ?: '#1f2a44' }}">
                                <span class="inline-flex rounded-full bg-white/95 px-3.5 py-1 text-xs font-semibold text-navy">
                                    {{ angka_id($focus->programs_count) }} program
                                </span>
                                <h3 class="mt-5 font-display text-xl font-semibold text-white">{{ $focus->name }}</h3>
                            </div>

                            <div class="flex flex-1 flex-col p-7">
                                <p class="card-excerpt text-base leading-relaxed text-ink-soft">{{ ringkas_isi(null, $focus->description, 170) }}</p>
                                <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-terracotta">
                                    Lihat program
                                    <span aria-hidden="true">→</span>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= SEJARAH ================= --}}
    {{--
        Kronologi peristiwa penting organisasi. Tahun tampil sebagai tombol
        .chip yang memilih kartu peristiwa — pola tab yang sama seperti bagian
        Struktur Pengurus (perilaku ditambahkan bertahap lewat [data-tabs] pada
        resources/js/app.js), sehingga tanpa JavaScript seluruh peristiwa tetap
        tampil berurutan dan tetap terbaca mesin pencari. Kartu memakai bahasa
        rupa situs: navy, terracotta, gold, cream, dan huruf Poppins.
    --}}
    @if ($histories->count())
        <section id="sejarah" class="section-soft section-y">
            <div class="container-page">
                <div class="mx-auto max-w-3xl text-center" data-reveal>
                    <p class="section-eyebrow">Sejarah</p>
                    <h2 class="section-title mt-4 text-3xl md:text-4xl">Perjalanan PERWAMA INA</h2>
                    <p class="mt-4 text-base leading-relaxed text-ink-soft">
                        Dari pertemuan pertama para perempuan Maluku hingga jaringan pengurus cabang di berbagai
                        provinsi — inilah tonggak yang membentuk PERWAMA INA hari ini.
                    </p>
                </div>

                <div class="mt-12" data-tabs>
                    <div class="flex flex-wrap justify-center gap-3" role="tablist" aria-label="Tahun peristiwa penting">
                        @foreach ($histories as $history)
                            @php
                                // Kunci tab memakai id baris agar tetap unik bila ada dua tahun yang sama.
                                $kunci = 'sejarah-'.$history->id;
                            @endphp
                            <button type="button"
                                    class="chip {{ $loop->first ? 'is-active' : '' }}"
                                    id="tab-{{ $kunci }}"
                                    data-tab="{{ $kunci }}"
                                    role="tab"
                                    aria-controls="panel-{{ $kunci }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <span class="chip-dot" aria-hidden="true"></span>
                                {{ $history->year ?: 'Peristiwa '.$loop->iteration }}
                            </button>
                        @endforeach
                    </div>

                    @foreach ($histories as $history)
                        @php
                            $kunci = 'sejarah-'.$history->id;
                        @endphp
                        <div class="mt-10"
                             id="panel-{{ $kunci }}"
                             data-tab-panel="{{ $kunci }}"
                             role="tabpanel"
                             aria-labelledby="tab-{{ $kunci }}"
                             tabindex="0">
                            <article class="history-card" data-reveal>
                                <div class="history-card-body">
                                    <p class="history-badge">
                                        {{ $history->year ? 'Tahun '.$history->year : 'Peristiwa' }}@if ($history->location) — {{ $history->location }}@endif
                                    </p>

                                    <h3 class="history-title">{{ $history->title }}</h3>

                                    @if ($history->description)
                                        {{-- Deskripsi diisi lewat editor kaya (RichEditor) pada panel admin. --}}
                                        <div class="prose-perwama mt-4 text-base">
                                            {!! $history->description !!}
                                        </div>
                                    @endif

                                    @if ($history->location)
                                        <p class="history-place">
                                            <svg class="h-4 w-4 shrink-0 text-terracotta" fill="none" stroke="currentColor"
                                                 stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 21s7-5.686 7-11a7 7 0 1 0-14 0c0 5.314 7 11 7 11z"/>
                                                <circle cx="12" cy="10" r="2.5"/>
                                            </svg>
                                            <span>
                                                Lokasi peristiwa:
                                                <span class="font-semibold text-navy">{{ $history->location }}</span>
                                            </span>
                                        </p>
                                    @endif
                                </div>

                                {{--
                                    Sisi foto: panel navy dengan foto menutupi penuh. Bila foto belum
                                    diunggah, judul beserta tahun tetap tampil agar kartu tidak kosong
                                    (senada dengan .media-placeholder pada kartu lain).
                                --}}
                                <div class="history-media">
                                    @if ($history->image)
                                        <img src="{{ media_url($history->image) }}"
                                             alt="{{ $history->title }}@if ($history->year) {{ $history->year }}@endif"
                                             loading="lazy">
                                    @else
                                        <p class="history-media-caption">
                                            {{ $history->title }}@if ($history->year) {{ $history->year }}@endif
                                        </p>
                                    @endif
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= KEPENGURUSAN ================= --}}
    {{--
        Struktur pengurus dikelompokkan per tingkat dan dipilih lewat tab.
        Tab memakai tombol .chip dengan penanda .is-active (sama seperti
        penyaring pada halaman daftar); perilakunya ditambahkan bertahap oleh
        resources/js/app.js melalui [data-tabs], sehingga bila JavaScript tidak
        aktif seluruh tingkat tetap tampil lengkap.
    --}}
    @if ($management->isNotEmpty())
        @php
            // Hanya tingkat yang benar-benar memiliki pengurus yang ditampilkan.
            $tingkatTerisi = collect($levels)->filter(
                fn (string $label, string $key): bool => $management->get($key, collect())->isNotEmpty(),
            );
        @endphp

        {{--
            Latar bagian kepemimpinan memakai warna halaman — sama seperti
            bagian Fokus Gerakan — bukan pita .section-soft, sehingga bagian
            ini menyatu dengan bagian sekitarnya.
        --}}
        <section id="struktur-pengurus" class="section-y">
            <div class="container-page">
                <div class="mx-auto max-w-3xl text-center" data-reveal>
                    <p class="section-eyebrow">Kepemimpinan</p>
                    <h2 class="section-title mt-4 text-3xl md:text-4xl">Struktur Pengurus</h2>
                    <p class="mt-4 text-base leading-relaxed text-ink-soft">
                        Para tokoh perempuan yang menggerakkan dan mengarahkan jalannya organisasi.
                    </p>
                </div>

                @if ($tingkatTerisi->count() > 1)
                    <div data-tabs>
                        <div class="mt-10 flex flex-wrap justify-center gap-2" role="tablist" aria-label="Tingkat kepengurusan">
                            @foreach ($tingkatTerisi as $key => $label)
                                <button type="button"
                                        class="chip {{ $loop->first ? 'is-active' : '' }}"
                                        id="tab-{{ $key }}"
                                        data-tab="{{ $key }}"
                                        role="tab"
                                        aria-controls="panel-{{ $key }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>

                        @foreach ($tingkatTerisi as $key => $label)
                            <div class="mt-12"
                                 id="panel-{{ $key }}"
                                 data-tab-panel="{{ $key }}"
                                 role="tabpanel"
                                 aria-labelledby="tab-{{ $key }}">
                                <x-leadership-level :people="$management->get($key)" :label="$label" />
                            </div>
                        @endforeach
                    </div>
                @else
                    @foreach ($tingkatTerisi as $key => $label)
                        <div class="mt-12">
                            <x-leadership-level :people="$management->get($key)" :label="$label" />
                        </div>
                    @endforeach
                @endif
            </div>
        </section>
    @endif

    {{-- ================= CTA ================= --}}
    {{--
        Bagian penutup halaman. Jeda atas dan jeda bawahnya dibuat sama besar
        supaya kartu tidak tampak menempel pada bagian di atasnya lalu melayang
        jauh dari pita footer.

        Jeda atas = padding bawah bagian sebelumnya (.section-y: 5rem di ponsel,
        6.25rem di layar besar) + padding atas bagian ini (pt-20 / md:pt-25,
        nilainya mengikuti .section-y) = 10rem / 12.5rem — persis ritme antar
        bagian di halaman ini. Padding bawah kartu (pb-40 = 10rem, md:pb-50 =
        12.5rem) dipatok sebesar jumlah tadi, jadi jaraknya ke bagian di atas
        sama dengan jaraknya ke footer. Ritme penutup ini sama dengan CTA
        beranda dan halaman Cerita.
    --}}
    <section class="pt-20 pb-40 md:pt-25 md:pb-50">
        <div class="container-page">
            <div class="relative isolate overflow-hidden rounded-3xl bg-navy px-8 py-14 text-center md:px-16">
                <div class="hero-pattern absolute inset-0" aria-hidden="true"></div>
                <div class="relative mx-auto max-w-2xl">
                    <h2 class="font-display text-3xl font-semibold text-white md:text-4xl">Ingin tahu lebih banyak?</h2>
                    <p class="mt-4 text-base text-white/75">
                        Hubungi sekretariat kami atau bergabung menjadi bagian dari keluarga besar PERWAMA INA.
                    </p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('contact') }}" class="btn btn-gold">Hubungi Kami</a>
                        <a href="{{ route('program.index') }}" class="btn btn-outline">Lihat Program</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

