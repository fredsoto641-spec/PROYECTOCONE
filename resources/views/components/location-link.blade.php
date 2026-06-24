@props([
    'link',
])

<a href="{{ $link['href'] }}" class="rounded-full border border-[#E5E7EB] bg-[#F8F8F8] px-3.5 py-2 text-sm font-semibold text-[#6B7280] transition duration-200 hover:border-[#E91E63]/40 hover:bg-[#E91E63]/5 hover:text-[#E91E63] focus:outline-none focus:ring-2 focus:ring-[#E91E63] focus:ring-offset-2">
    {{ $link['label'] }}
</a>
