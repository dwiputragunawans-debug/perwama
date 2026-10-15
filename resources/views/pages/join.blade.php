@extends('layouts.app')

@section('title', 'Bergabung')

@section('content')
    <x-page-hero
        eyebrow="Bergabung"
        title="Bergabung Bersama PERWAMA INA"
        subtitle="Menjadi bagian dari gerakan pemberdayaan perempuan Maluku di seluruh Indonesia."
        :breadcrumbs="['Beranda' => route('home'), 'Bergabung' => route('join')]" />

    {{-- ================= ALASAN BERGABUNG ================= --}}
    <section class="section-y">
        <div class="container-page">
            <div class="max-w-3xl">
                <p class="section-eyebrow">Mengapa bergabung</p>
                <h2 class="section-title mt-3 text-2xl md:text-3xl">Satu langkah, banyak manfaat</h2>
                <p class="mt-4 text-base leading-relaxed text-ink-soft">
                    PERWAMA INA membuka pintu bagi siapa pun yang memiliki semangat untuk memajukan
                    perempuan Maluku — baik sebagai anggota, relawan, mitra, maupun kolaborator.
                </p>
            </div>

            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['title' => 'Jaringan Nasional', 'body' => 'Terhubung dengan perempuan Maluku dari seluruh Indonesia, dari Dewan Pengurus Pusat hingga pengurus cabang kota dan kabupaten. Jejaring ini membuka akses informasi, kerja sama usaha, dan dukungan lintas daerah.'],
                    ['title' => 'Peningkatan Kapasitas', 'body' => 'Ikut pelatihan, pendampingan, dan sekolah perempuan yang kami selenggarakan secara berkala. Materinya mencakup kepemimpinan, literasi digital, manajemen usaha, hingga advokasi kebijakan.'],
                    ['title' => 'Kontribusi Nyata', 'body' => 'Terlibat langsung dalam program advokasi, kewirausahaan, pendidikan, dan literasi digital. Setiap kontribusi anggota kami arahkan pada kebutuhan perempuan Maluku yang paling mendesak.'],
                    ['title' => 'Solidaritas', 'body' => 'Dapatkan dukungan moral dan jejaring ketika menghadapi tantangan bersama. Dalam situasi sulit, anggota tidak berjalan sendiri — ada komunitas yang siap mendampingi.'],
                ] as $benefit)
                    <div class="card p-7" data-reveal>
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gold/20 font-display text-xl text-navy">
                            {{ $loop->iteration }}
                        </span>
                        <h3 class="card-title mt-5 font-display text-xl leading-snug text-navy">{{ $benefit['title'] }}</h3>
                        <p class="mt-4 text-base leading-relaxed text-ink-soft">{{ $benefit['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= STATISTIK ================= --}}
    @if ($statistics->count())
        <section class="section-soft py-14">
            <div class="container-page grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($statistics as $stat)
                    <div class="text-center">
                        <p class="font-display text-3xl font-semibold text-navy md:text-4xl">
                            {{ $stat->value }}@if ($stat->suffix) {{ $stat->suffix }}@endif
                        </p>
                        <p class="mt-2 text-sm font-semibold tracking-[0.01em] text-ink-soft">{{ $stat->label }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= SYARAT & ALUR ================= --}}
    <section class="section-y">
        <div class="container-page grid gap-12 lg:grid-cols-2">
            <div>
                <p class="section-eyebrow">Persyaratan</p>
                <h2 class="section-title mt-3 text-2xl md:text-3xl">Syarat keanggotaan</h2>
                <ul class="mt-6 space-y-3 text-base leading-relaxed text-ink-soft">
                    @foreach ([
                        'Perempuan dengan keturunan Maluku atau berdomisili di wilayah Maluku.',
                        'Berusia minimal 17 tahun atau telah menikah.',
                        'Bersedia mengikuti AD/ART dan kode etik organisasi.',
                        'Memiliki komitmen untuk aktif dalam kegiatan organisasi.',
                        'Mengisi formulir dengan data yang benar dan dapat dipertanggungjawabkan.',
                    ] as $requirement)
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2 w-2 flex-none rounded-full bg-gold" aria-hidden="true"></span>
                            <span>{{ $requirement }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="section-eyebrow">Alur pendaftaran</p>
                <h2 class="section-title mt-3 text-2xl md:text-3xl">Empat langkah mudah</h2>
                <ol class="mt-6 space-y-5">
                    @foreach ([
                        ['title' => 'Isi formulir', 'body' => 'Lengkapi data diri dan pilih jenis keikutsertaan yang Anda inginkan.'],
                        ['title' => 'Verifikasi data', 'body' => 'Tim kami meninjau kelengkapan dan keabsahan data yang Anda kirim.'],
                        ['title' => 'Konfirmasi', 'body' => 'Anda akan dihubungi melalui WhatsApp atau email untuk proses lanjutan.'],
                        ['title' => 'Aktif berkegiatan', 'body' => 'Terima kartu anggota dan mulai terlibat dalam program organisasi.'],
                    ] as $step)
                        <li class="flex gap-4">
                            <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-navy font-display text-sm font-semibold text-white">
                                {{ $loop->iteration }}
                            </span>
                            <span>
                                <span class="block font-display text-base font-semibold text-navy">{{ $step['title'] }}</span>
                                <span class="mt-1 block text-base leading-relaxed text-ink-soft">{{ $step['body'] }}</span>
                            </span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- ================= FORMULIR PENDAFTARAN ================= --}}
    <section id="formulir" class="section-soft section-y">
        <div class="container-page">
            <div class="mx-auto max-w-3xl text-center">
                <p class="section-eyebrow">Formulir</p>
                <h2 class="section-title mt-3 text-2xl md:text-3xl">Formulir pendaftaran anggota & relawan</h2>
                <p class="mt-4 text-base leading-relaxed text-ink-soft">
                    Lengkapi data berikut dengan benar. Kolom bertanda <span class="text-terracotta">*</span> wajib diisi.
                </p>
            </div>

            <div class="mx-auto mt-8 max-w-3xl">
                <x-alert type="success" class="mb-6" />
            </div>

            <form action="{{ route('join.store') }}" method="POST" class="mx-auto mt-8 grid max-w-3xl gap-8">
                @csrf

                <fieldset class="rounded-2xl border border-navy/10 bg-white p-6 md:p-8" aria-labelledby="fieldset-data-diri">
                    <h3 id="fieldset-data-diri" class="font-display text-lg font-semibold text-navy">Data Diri</h3>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="full_name" class="field-label">Nama lengkap <span class="text-terracotta">*</span></label>
                            <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}"
                                   required maxlength="255" class="field-input" autocomplete="name">
                        </div>

                        <div>
                            <label for="nik" class="field-label">NIK</label>
                            <input id="nik" type="text" name="nik" value="{{ old('nik') }}"
                                   maxlength="32" inputmode="numeric" class="field-input">
                        </div>

                        <div>
                            <label for="birth_place" class="field-label">Tempat lahir</label>
                            <input id="birth_place" type="text" name="birth_place" value="{{ old('birth_place') }}"
                                   maxlength="120" class="field-input">
                        </div>

                        <div>
                            <label for="birth_date" class="field-label">Tanggal lahir</label>
                            <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" class="field-input">
                        </div>

                        <div>
                            <label for="gender" class="field-label">Jenis kelamin</label>
                            <select id="gender" name="gender" class="field-input">
                                <option value="">— Pilih —</option>
                                @foreach (['Perempuan', 'Laki-laki'] as $gender)
                                    <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ $gender }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="whatsapp" class="field-label">Nomor WhatsApp <span class="text-terracotta">*</span></label>
                            <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp') }}"
                                   required maxlength="30" class="field-input" placeholder="0812xxxxxxx" autocomplete="tel">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="email" class="field-label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   maxlength="255" class="field-input" autocomplete="email">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="field-label">Alamat</label>
                            <input id="address" type="text" name="address" value="{{ old('address') }}"
                                   maxlength="500" class="field-input" autocomplete="street-address">
                        </div>

                        <div>
                            <label for="city" class="field-label">Kota/Kabupaten domisili</label>
                            <input id="city" type="text" name="city" value="{{ old('city') }}" maxlength="120" class="field-input">
                        </div>

                        <div>
                            <label for="province" class="field-label">Provinsi</label>
                            <input id="province" type="text" name="province" value="{{ old('province') }}"
                                   maxlength="120" list="provinsi-list" class="field-input">
                            <datalist id="provinsi-list">
                                @foreach ($provinces as $province)
                                    <option value="{{ $province->name }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                </fieldset>


                <fieldset class="rounded-2xl border border-navy/10 bg-white p-6 md:p-8" aria-labelledby="fieldset-latar-belakang">
                    <h3 id="fieldset-latar-belakang" class="font-display text-lg font-semibold text-navy">Latar Belakang & Minat</h3>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="education" class="field-label">Pendidikan terakhir</label>
                            <input id="education" type="text" name="education" value="{{ old('education') }}"
                                   maxlength="120" class="field-input" placeholder="SMA / D3 / S1 / S2">
                        </div>

                        <div>
                            <label for="occupation" class="field-label">Pekerjaan</label>
                            <input id="occupation" type="text" name="occupation" value="{{ old('occupation') }}"
                                   maxlength="120" class="field-input">
                        </div>

                        <div>
                            <label for="interest_field" class="field-label">Bidang minat</label>
                            <input id="interest_field" type="text" name="interest_field" value="{{ old('interest_field') }}"
                                   maxlength="120" class="field-input" placeholder="Pendidikan, UMKM, advokasi…">
                        </div>

                        <div>
                            <label for="maluku_origin" class="field-label">Asal daerah di Maluku</label>
                            <input id="maluku_origin" type="text" name="maluku_origin" value="{{ old('maluku_origin') }}"
                                   maxlength="120" class="field-input" placeholder="Contoh: Tual, Maluku Tenggara">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="domicile_region" class="field-label">Wilayah domisili saat ini</label>
                            <input id="domicile_region" type="text" name="domicile_region" value="{{ old('domicile_region') }}"
                                   maxlength="120" class="field-input" placeholder="Contoh: Jakarta Selatan">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="rounded-2xl border border-navy/10 bg-white p-6 md:p-8" aria-labelledby="fieldset-keikutsertaan">
                    <h3 id="fieldset-keikutsertaan" class="font-display text-lg font-semibold text-navy">Keikutsertaan</h3>

                    <div class="mt-4">
                        <label for="membership_type" class="field-label">Jenis keikutsertaan <span class="text-terracotta">*</span></label>
                        <select id="membership_type" name="membership_type" required class="field-input">
                            @foreach (['anggota' => 'Anggota', 'relawan' => 'Relawan', 'mitra' => 'Mitra', 'kolaborasi' => 'Kolaborasi'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('membership_type', $form) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-5">
                        <label for="motivation" class="field-label">Motivasi bergabung</label>
                        <textarea id="motivation" name="motivation" rows="5" maxlength="2000"
                                  class="field-input" placeholder="Ceritakan alasan dan harapan Anda bergabung dengan PERWAMA INA…">{{ old('motivation') }}</textarea>
                    </div>

                    <label class="mt-6 flex items-start gap-3 meta">
                        <input type="checkbox" required class="mt-1 h-4 w-4 rounded border-navy/30 text-navy focus:ring-navy">
                        <span>Saya menyatakan data yang saya kirim adalah benar dan bersedia mengikuti ketentuan organisasi.</span>
                    </label>

                    <button type="submit" class="btn btn-navy mt-7 w-full sm:w-auto">Kirim pendaftaran</button>
                </fieldset>
            </form>
        </div>
    </section>

@endsection

