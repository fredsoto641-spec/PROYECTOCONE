@props([
    'publications' => [],
])

@php
    $publications = collect($publications)->values()->all();
    $items = array_merge($publications, $publications);
@endphp

<div class="relative overflow-hidden rounded-[2rem] border border-[#E5E7EB] bg-white py-4 shadow-sm">
    <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-16 bg-gradient-to-r from-white to-transparent"></div>
    <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-16 bg-gradient-to-l from-white to-transparent"></div>

    <div class="marquee-track flex w-max gap-4 px-4">
        @forelse ($items as $publication)
            <x-latest-publication-card :publication="$publication" />
        @empty
            <div class="w-72 rounded-2xl border border-dashed border-[#E5E7EB] bg-[#F8F8F8] p-4 text-sm font-semibold text-[#6B7280]">
                Todavía no hay publicaciones activas.
            </div>
        @endforelse
    </div>
</div>
