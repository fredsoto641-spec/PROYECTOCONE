@props([
    'logoPrimary',
    'logoAccent',
    'logoHref',
    'links' => [],
    'loginHref' => '#',
    'loginLabel',
    'publishHref' => '#',
    'publishLabel',
])

@php
    $logo = trim($logoPrimary.' '.$logoAccent);
    $user = auth()->user();
    $dashboardHref = \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : url('/');
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-50 border-b border-[#E5E7EB]/80 bg-white/95 shadow-sm backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Navegación principal">
        <a href="{{ $logoHref }}" class="flex min-w-0 items-center gap-2 text-lg font-black tracking-normal text-[#222222]" aria-label="{{ $logo }}">
            <span class="grid size-9 place-items-center rounded-2xl bg-[#E91E63]" aria-hidden="true">
                <x-application-logo class="size-7" />
            </span>
            <span class="inline-flex min-w-0 items-baseline gap-1 text-3xl leading-none" style="font-family: 'Dancing Script', cursive !important; font-weight: 700;">
                <span style="color: var(--site-text);">{{ $logoPrimary }}</span>
                @if ($logoAccent !== '')
                    <span style="color: var(--site-primary);">{{ $logoAccent }}</span>
                @endif
            </span>
        </a>

        <div class="public-nav-desktop items-center gap-8">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}" class="text-sm font-medium text-[#6B7280] transition hover:text-[#E91E63]">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <div class="public-nav-desktop items-center gap-2">
            @auth
                <x-button :href="$dashboardHref" variant="ghost" size="sm" class="max-w-48 truncate">
                    {{ $user->name }}
                </x-button>
            @else
                <x-button :href="$loginHref" variant="ghost" size="sm">{{ $loginLabel }}</x-button>
                <x-button :href="$publishHref" size="sm">{{ $publishLabel }}</x-button>
            @endauth
        </div>

        <button
            type="button"
            class="public-nav-mobile-toggle size-10 items-center justify-center rounded-md text-[#6B7280] transition hover:bg-[#F8F8F8] hover:text-[#E91E63] focus:outline-none focus:ring-2 focus:ring-[#E91E63] focus:ring-offset-2"
            aria-label="Abrir menú"
            x-on:click="open = ! open"
            x-bind:aria-expanded="open.toString()"
        >
            <x-heroicon-o-bars-3 x-show="!open" class="h-6 w-6" aria-hidden="true" />
            <x-heroicon-o-x-mark x-show="open" x-cloak class="h-6 w-6" aria-hidden="true" />
        </button>
    </nav>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="public-nav-mobile-panel border-t border-[#E5E7EB] bg-white"
    >
        <div class="mx-auto max-w-7xl space-y-1 px-4 py-3 sm:px-6 lg:px-8">
            @foreach ($links as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="block rounded-md px-3 py-2 text-sm font-medium text-[#6B7280] transition hover:bg-[#F8F8F8] hover:text-[#E91E63]"
                    x-on:click="open = false"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <div class="mx-auto flex max-w-7xl flex-col gap-2 border-t border-[#E5E7EB] px-4 py-4 sm:px-6 lg:px-8">
            @auth
                <x-button :href="$dashboardHref" variant="ghost" size="sm" class="w-full">
                    {{ $user->name }}
                </x-button>
            @else
                <x-button :href="$loginHref" variant="ghost" size="sm" class="w-full">{{ $loginLabel }}</x-button>
                <x-button :href="$publishHref" size="sm" class="w-full">{{ $publishLabel }}</x-button>
            @endauth
        </div>
    </div>
</header>
