@props(['type' => 'success', 'message' => null])

@php
    $styles = [
        'success' => 'border-green bg-green/10 text-green',
        'error' => 'border-terracotta bg-terracotta/10 text-terracotta',
        'info' => 'border-navy bg-navy/10 text-navy',
    ];
@endphp

@if ($message || session()->has($type))
    <div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border px-4 py-3 text-sm font-medium '.($styles[$type] ?? $styles['info'])]) }}
         role="status">
        <span aria-hidden="true">&bull;</span>
        <span>{{ $message ?? session($type) }}</span>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="rounded-xl border border-terracotta bg-terracotta/10 px-4 py-3 text-sm text-terracotta" role="alert">
        <p class="font-semibold">Mohon periksa kembali data yang Anda kirim:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
