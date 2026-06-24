@props([
    'publication',
])

<a href="{{ $publication['href'] }}" class="group flex w-72 shrink-0 items-center gap-4 rounded-2xl border border-[#E5E7EB] bg-[#F8F8F8] p-4 transition duration-300 hover:-translate-y-0.5 hover:border-[#E91E63]/30 hover:bg-white hover:shadow-lg hover:shadow-[#222222]/10">
    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-[#E91E63]/10 text-[#E91E63]" aria-hidden="true">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M7 7h10M7 12h10M7 17h6" stroke-linecap="round" />
        </svg>
    </span>

    <span class="min-w-0">
        <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-[#E91E63]">
            <span>{{ $publication['time'] }}</span>
            @if ($publication['verified'])
                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] text-emerald-700 ring-1 ring-emerald-200">{{ $publication['verifiedLabel'] }}</span>
            @endif
        </span>
        <span class="mt-1 block truncate text-sm font-bold text-[#222222]">{{ $publication['title'] }}</span>
        <span class="mt-1 block truncate text-sm text-[#6B7280]">{{ $publication['city'] }} · {{ $publication['category'] }}</span>
    </span>
</a>
