@props([
    'brand',
    'brandInitials',
    'brandHref',
    'description',
    'copyright',
    'groups' => null,
    'legalLinks' => null,
])

@php
    if ($groups === null || $legalLinks === null) {
        $footerSettings = \App\Models\SiteSetting::current();
        $groups = $groups ?? $footerSettings->footerGroups();
        $legalLinks = $legalLinks ?? $footerSettings->footerLegalLinks();
    }
@endphp

<footer class="border-t border-[#E5E7EB] bg-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-5 lg:px-8">
        <div class="lg:col-span-1">
            <a href="{{ $brandHref }}" class="flex items-center gap-2 text-lg font-black text-[#222222]">
                <span class="grid size-9 place-items-center rounded-2xl bg-[#E91E63] text-sm font-black text-white">{{ $brandInitials }}</span>
                <span>{{ $brand }}</span>
            </a>
            <p class="mt-4 text-sm leading-6 text-[#6B7280]">{{ $description }}</p>
        </div>

        @foreach ($groups as $title => $items)
            <div>
                <h3 class="text-sm font-bold text-[#222222]">{{ $title }}</h3>
                <ul class="mt-4 space-y-3">
                    @foreach ($items as $item)
                        <li>
                            <a href="{{ $item['href'] }}" class="text-sm text-[#6B7280] transition hover:text-[#E91E63]">{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <div class="border-t border-[#E5E7EB] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 text-sm text-[#6B7280] sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ $brand }}. {{ $copyright }}</p>
            <div class="flex gap-4">
                @foreach ($legalLinks as $link)
                    <a href="{{ $link['href'] }}" class="transition hover:text-[#E91E63]">{{ $link['label'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
</footer>
