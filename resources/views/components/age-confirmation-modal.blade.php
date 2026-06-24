@props([
    'content',
])

@if ($content['enabled'] ?? true)
<div
    x-data="{
        open: false,
        init() {
            this.open = localStorage.getItem('{{ $content['storageKey'] }}') !== 'true';
            document.documentElement.classList.toggle('overflow-hidden', this.open);
        },
        confirm() {
            localStorage.setItem('{{ $content['storageKey'] }}', 'true');
            this.open = false;
            document.documentElement.classList.remove('overflow-hidden');
        }
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] flex min-h-screen items-center justify-center px-4 py-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="age-confirmation-title"
>
    <div class="absolute inset-0 bg-[#222222]/55 backdrop-blur-md"></div>

    <div
        x-show="open"
        x-transition:enter="duration-200 ease-out"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        class="relative w-full max-w-lg overflow-hidden rounded-[2rem] border border-white/70 bg-white p-6 text-center shadow-2xl shadow-black/25 sm:p-8"
    >
        <div class="mx-auto grid size-16 place-items-center rounded-3xl bg-[#E91E63]/10 text-2xl font-black text-[#E91E63]">
            {{ $content['badge'] }}
        </div>

        <h2 id="age-confirmation-title" class="mt-6 text-2xl font-black tracking-normal text-[#222222] sm:text-3xl">
            {{ $content['title'] }}
        </h2>

        <p class="mt-4 text-sm leading-7 text-[#6B7280] sm:text-base">
            {{ $content['description'] }}
        </p>

        <div class="mt-7 grid gap-3 sm:grid-cols-2">
            <x-button href="{{ $content['exitHref'] }}" variant="secondary" size="lg" class="w-full">
                {{ $content['exitLabel'] }}
            </x-button>
            <x-button type="button" size="lg" class="w-full" x-on:click="confirm()">
                {{ $content['confirmLabel'] }}
            </x-button>
        </div>

        <p class="mt-5 text-xs leading-5 text-[#6B7280]">
            {{ $content['legalText'] }}
        </p>
    </div>
</div>
@endif
