@extends('layouts.app')

@section('title', 'Sesi Berakhir')

@section('content')
    <section class="section-y">
        <div class="container-page">
            <div class="mx-auto max-w-2xl text-center">
                <p class="section-eyebrow">Kesalahan 419</p>
                <h1 class="section-title mt-4 text-3xl md:text-4xl">Sesi Anda sudah berakhir</h1>
                <div class="gold-rule mx-auto mt-6"></div>

                <p class="mt-6 text-base leading-relaxed text-ink-soft">
                    Halaman ini sudah terlalu lama dibiarkan terbuka sehingga sesi keamanannya berakhir.
                    Isian yang belum terkirim tidak tersimpan. Silakan kembali ke formulir, lengkapi ulang,
                    lalu kirim kembali.
                </p>

                <p class="mt-4 text-base leading-relaxed text-ink-soft">
                    Bila pesan ini muncul terus-menerus, muat ulang halaman dengan
                    <strong>Ctrl/Cmd + Shift + R</strong> sebelum mengisi formulir.
                </p>

                <div class="mt-9 flex flex-wrap justify-center gap-4">
                    <a href="{{ route('join') }}" class="btn btn-gold">Kembali ke formulir bergabung</a>
                    <a href="{{ route('home') }}" class="btn btn-quiet">Ke beranda</a>
                </div>
            </div>
        </div>
    </section>
@endsection
