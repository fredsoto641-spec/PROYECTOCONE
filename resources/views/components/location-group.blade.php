@props([
    'group',
])

<article class="rounded-3xl border border-[#E5E7EB] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#222222]/10">
    <div class="flex items-start justify-between gap-4 border-b border-[#E5E7EB] pb-4">
        <div>
            <h3 class="text-lg font-black text-[#222222]">{{ $group['title'] }}</h3>
            <p class="mt-1 text-sm text-[#6B7280]">{{ $group['description'] }}</p>
        </div>
        <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-[#E91E63]/10 text-[#E91E63]" aria-hidden="true">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11Z" stroke-linejoin="round" />
                <path d="M12 10.5h.01" stroke-linecap="round" />
            </svg>
        </span>
    </div>

    <div class="mt-5 flex flex-wrap gap-2">
        @forelse ($group['links'] as $link)
            <x-location-link :link="$link" />
        @empty
            <p class="text-sm text-[#9CA3AF]">Aún no hay ubicaciones disponibles.</p>
        @endforelse
    </div>
</article>
