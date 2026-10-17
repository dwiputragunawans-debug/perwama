@extends('layouts.app')

@section('title', 'Kontak')

@section('content')
    <x-page-hero
        eyebrow="Kontak"
        title="Hubungi PERWAMA INA"
        subtitle="Sampaikan pertanyaan, aspirasi, atau rencana kerja sama Anda. Kami siap menindaklanjuti."
        :breadcrumbs="['Beranda' => route('home'), 'Kontak' => route('contact')]" />

    <section class="section-y">
        <div class="container-page grid gap-12 lg:grid-cols-[1fr_1.2fr]">
            {{-- ================= INFORMASI KONTAK ================= --}}
            {{-- Daftar informasi kontak: pada layar sempit label ditumpuk di
                 atas nilainya (`flex-col` → `sm:flex-row`) supaya nilai memakai
                 lebar penuh. `min-w-0` pada kolom kisi dan setiap `<dd>` wajib:
                 tanpa itu kolom mengambil lebar min-content dari surel
                 terpanjang sehingga seluruh isi meluber melewati padding kanan
                 `.container-page` pada layar ±340px. --}}
            <div class="min-w-0">
                <p class="section-eyebrow">Sekretariat</p>
                <h2 class="section-title mt-3 text-2xl md:text-3xl">{{ $profile->name }}</h2>

                <dl class="mt-8 space-y-5 text-sm">
                    @if ($profile->address)
                        <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                            <dt class="text-sm font-semibold tracking-[0.01em] text-ink-soft sm:w-28 sm:flex-none">Alamat</dt>
                            <dd class="min-w-0 wrap-anywhere leading-relaxed text-ink-soft">
                                {{ $profile->address }}
                                @if ($profile->city), {{ $profile->city }}@endif
                                @if ($profile->province) {{ $profile->province }}@endif
                                @if ($profile->postal_code) {{ $profile->postal_code }}@endif
                            </dd>
                        </div>
                    @endif

                    @if ($profile->email)
                        <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                            <dt class="text-sm font-semibold tracking-[0.01em] text-ink-soft sm:w-28 sm:flex-none">Email</dt>
                            <dd class="min-w-0 wrap-anywhere"><a href="mailto:{{ $profile->email }}" class="text-navy transition hover:text-terracotta">{{ $profile->email }}</a></dd>
                        </div>
                    @endif

                    @if ($profile->phone)
                        <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                            <dt class="text-sm font-semibold tracking-[0.01em] text-ink-soft sm:w-28 sm:flex-none">Telepon</dt>
                            <dd class="min-w-0 wrap-anywhere text-ink-soft">{{ $profile->phone }}</dd>
                        </div>
                    @endif

                    @if ($profile->whatsapp)
                        <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                            <dt class="text-sm font-semibold tracking-[0.01em] text-ink-soft sm:w-28 sm:flex-none">WhatsApp</dt>
                            <dd class="min-w-0 wrap-anywhere">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->whatsapp) }}"
                                   target="_blank" rel="noopener" class="text-navy transition hover:text-terracotta">
                                    {{ $profile->whatsapp }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>

                @php
                    $socials = array_filter([
                        'Facebook' => $profile->facebook,
                        'Instagram' => $profile->instagram,
                        'YouTube' => $profile->youtube,
                        'Twitter' => $profile->twitter,
                        'TikTok' => $profile->tiktok,
                    ]);
                @endphp

                @if ($socials)
                    <div class="mt-8">
                        <p class="text-sm font-semibold tracking-[0.01em] text-ink-soft">Media sosial</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($socials as $label => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="chip">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($provinces->isNotEmpty())
                    <div class="mt-10 rounded-2xl border border-navy/10 bg-white p-6">
                        <p class="text-sm font-semibold tracking-[0.01em] text-terracotta">Koordinasi wilayah</p>
                        <p class="mt-2 text-base leading-relaxed text-ink-soft">
                            Kami hadir di {{ $provinces->count() }} provinsi melalui pengurus wilayah dan perwakilan kota.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($provinces->take(8) as $province)
                                <a href="{{ route('region.show', $province->slug) }}" class="chip">{{ $province->name }}</a>
                            @endforeach
                        </div>
                        <a href="{{ route('region.index') }}" class="btn btn-outline mt-5 w-full">Lihat semua wilayah</a>
                    </div>
                @endif
            </div>

            {{-- ================= FORMULIR ================= --}}
            <div class="min-w-0">
                <div class="rounded-2xl border border-navy/10 bg-white p-6 md:p-8">
                    <p class="section-eyebrow">Formulir</p>
                    <h2 class="section-title mt-3 text-2xl">Kirim pesan</h2>
                    <p class="mt-3 text-base leading-relaxed text-ink-soft">
                        Isi formulir berikut, tim kami akan merespons pada hari kerja.
                    </p>

                    <x-alert type="success" class="mt-6" />

                    <form action="{{ route('contact.store') }}" method="POST" class="mt-6 grid gap-5 sm:grid-cols-2">
                        @csrf

                        <div>
                            <label for="c_name" class="field-label">Nama <span class="text-terracotta">*</span></label>
                            <input id="c_name" type="text" name="name" value="{{ old('name') }}" required maxlength="255"
                                   class="field-input" autocomplete="name">
                        </div>

                        <div>
                            <label for="c_email" class="field-label">Email <span class="text-terracotta">*</span></label>
                            <input id="c_email" type="email" name="email" value="{{ old('email') }}" required maxlength="255"
                                   class="field-input" autocomplete="email">
                        </div>

                        <div>
                            <label for="c_phone" class="field-label">Telepon/WhatsApp</label>
                            <input id="c_phone" type="text" name="phone" value="{{ old('phone') }}" maxlength="30" class="field-input">
                        </div>

                        <div>
                            <label for="c_subject" class="field-label">Subjek</label>
                            <input id="c_subject" type="text" name="subject" value="{{ old('subject') }}" maxlength="255" class="field-input">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="c_message" class="field-label">Pesan <span class="text-terracotta">*</span></label>
                            <textarea id="c_message" name="message" rows="6" required maxlength="3000" class="field-input"
                                      placeholder="Tuliskan pesan Anda…">{{ old('message') }}</textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <button type="submit" class="btn btn-navy w-full sm:w-auto">Kirim pesan</button>
                        </div>
                    </form>
                </div>

                @if ($profile->map_embed)
                    <div class="mt-8 overflow-hidden rounded-2xl border border-navy/10 bg-white">
                        <div class="aspect-video [&>iframe]:h-full [&>iframe]:w-full">
                            {!! $profile->map_embed !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
