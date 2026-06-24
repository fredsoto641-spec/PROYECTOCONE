@props([
    'listings' => [],
])

@php
    $items = array_merge($listings, $listings);
@endphp

<div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-12 bg-gradient-to-r from-[#F8F8F8] to-transparent"></div>
    <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-12 bg-gradient-to-l from-[#F8F8F8] to-transparent"></div>

    <div class="marquee-track flex w-max gap-6">
        @foreach ($items as $listing)
            @php
                $marqueeListing = $listing;
                unset($marqueeListing['id']);
            @endphp

            <div class="w-72 shrink-0 md:w-80">
                <x-listing-card :listing="$marqueeListing" />
            </div>
        @endforeach
    </div>
</div>
