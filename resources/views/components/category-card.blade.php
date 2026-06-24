@props([
    'category',
])

<a href="{{ $category['href'] }}" class="group block overflow-hidden rounded-3xl border border-[#E5E7EB] bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#222222]/10">
    <div class="relative aspect-[16/10] overflow-hidden">
        <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-t from-black/45 to-transparent"></div>
    </div>
    <div class="flex items-center justify-between gap-4 p-5">
        <div>
            <h3 class="text-lg font-bold text-[#222222]">{{ $category['name'] }}</h3>
            <p class="mt-1 text-sm text-[#6B7280]">{{ $category['count'] }}</p>
        </div>
        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-[#E91E63]/10 text-[#E91E63] transition group-hover:bg-[#E91E63] group-hover:text-white" aria-hidden="true">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
    </div>
</a>
