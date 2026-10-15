@props([
    'image' => null,
    'alt' => '',
    'ratio' => 'grid',
    'eager' => false,
])

@php
    $ratioClass = match ($ratio) {
        'wide' => 'card-media-wide',
        'tall' => 'card-media-tall',
        'square' => 'card-media-square',
        default => 'card-media',
    };
@endphp

{{-- Satu tempat untuk mengatur rasio foto agar seluruh kartu konsisten. --}}
@if ($image)
    <img src="{{ $image }}"
         alt="{{ $alt }}"
         {{ $attributes->merge(['class' => $ratioClass.' transition duration-700 group-hover:scale-[1.03]']) }}
         loading="{{ $eager ? 'eager' : 'lazy' }}">
@else
    {{-- Foto asli belum tersedia: tampilkan bidang bermerek, bukan foto acak. --}}
    <div {{ $attributes->merge(['class' => $ratioClass.' media-placeholder']) }} aria-hidden="true">
        {{ Str::substr($alt ?: 'PERWAMA INA', 0, 1) }}
    </div>
@endif