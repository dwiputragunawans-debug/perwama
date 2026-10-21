@props([
    'eyebrow' => null,
    'title' => '',
    'subtitle' => null,
    'image' => null,
    'imageAlt' => null,
    'breadcrumbs' => [],
    'caption' => null,
])

<section class="relative isolate overflow-hidden bg-navy-deep">
    @if ($image)
        <img src="{{ $image }}" alt="{{ $imageAlt ?? '' }}" class="absolute inset-0 h-full w-full object-cover opacity-50" loading="eager">
    @endif
    <div class="hero-gradient absolute inset-0" aria-hidden="true"></div>

    <div class="container-page relative py-20 md:py-28">
        @if ($eyebrow)
            <div class="gold-rule"></div>
            <p class="section-eyebrow mt-5 text-gold">{{ $eyebrow }}</p>
        @endif

        <h1 class="mt-4 max-w-4xl font-display text-3xl font-bold leading-[1.15] text-white sm:text-4xl lg:text-[2.75rem]">
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="mt-6 max-w-2xl text-base leading-[1.7] text-white/90">
                {{ $subtitle }}
            </p>
        @endif

        @if ($caption)
            <p class="mt-6 max-w-xl text-sm italic text-white/80">{{ $caption }}</p>
        @endif

        @if (count($breadcrumbs) > 0)
            <nav class="mt-10 flex flex-wrap items-center gap-2.5 text-sm text-white/80" aria-label="Breadcrumb">
                @foreach ($breadcrumbs as $label => $url)
                    @if (! $loop->last)
                        <a href="{{ $url }}" class="transition hover:text-gold">{{ $label }}</a>
                        <span aria-hidden="true" class="text-white/40">/</span>
                    @else
                        <span class="font-semibold text-gold">{{ $label }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
    </div>
</section>
