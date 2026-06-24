@props([
    'listing',
])

<a
    href="{{ $listing['profileHref'] }}"
    class="group block h-full rounded-3xl focus:outline-none focus-visible:ring-4 focus-visible:ring-[#E91E63]/40 focus-visible:ring-offset-2"
    aria-label="Ver post: {{ $listing['title'] }}"
>
    <article
        @isset($listing['id']) id="{{ $listing['id'] }}" @endisset
        class="h-full overflow-hidden rounded-3xl border border-[#E5E7EB] bg-white shadow-sm transition duration-300 group-hover:-translate-y-1 group-hover:border-[#E91E63]/30 group-hover:shadow-xl group-hover:shadow-[#222222]/10"
    >
        <div class="relative aspect-[4/5] overflow-hidden">
            <img
                src="{{ $listing['image'] }}"
                alt="{{ $listing['title'] }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                loading="lazy"
            >
            <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                @if ($listing['verified'])
                    <x-badge variant="verified">Verificado</x-badge>
                @endif
                @if ($listing['featured'])
                    <x-badge variant="featured">Destacado</x-badge>
                @endif
            </div>
            @if ($listing['price'])
                <x-badge variant="neutral" class="absolute bottom-3 right-3">{{ $listing['price'] }}</x-badge>
            @endif
        </div>

        <div class="min-w-0 p-5">
            <div class="mb-3 flex min-w-0 items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-[#E91E63]">
                <span class="min-w-0 truncate">{{ $listing['city'] }}</span>
                <span class="size-1 rounded-full bg-[#E5E7EB]"></span>
                <span class="min-w-0 truncate">{{ $listing['category'] }}</span>
            </div>
            <h3
                class="truncate text-lg font-bold leading-7 text-[#222222] transition group-hover:text-[#E91E63]"
                title="{{ $listing['title'] }}"
            >
                {{ $listing['title'] }}
            </h3>
            <p
                class="mt-1 h-5 truncate text-sm leading-5 text-[#6B7280]"
                @if (filled($listing['subtitle'] ?? null)) title="{{ $listing['subtitle'] }}" @endif
            >
                {{ $listing['subtitle'] ?? '' }}
            </p>
            <p class="mt-3 truncate text-sm text-[#6B7280]">{{ $listing['updated'] }}</p>
        </div>
    </article>
</a>
