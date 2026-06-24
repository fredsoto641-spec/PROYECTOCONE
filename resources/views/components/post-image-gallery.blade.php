@props([
    'images',
    'title',
])

@php
    $images = collect($images)->filter()->values();
@endphp

@if ($images->isNotEmpty())
    <div
        x-data="{
            open: false,
            index: 0,
            images: @js($images->all()),
            returnFocus: null,
            openAt(index) {
                this.returnFocus = document.activeElement;
                this.index = Number(index) || 0;
                this.open = true;
                this.$nextTick(() => this.$refs.closeButton.focus());
            },
            close() {
                this.open = false;
                this.$nextTick(() => this.returnFocus?.focus());
            },
            previous() {
                this.index = (this.index - 1 + this.images.length) % this.images.length;
            },
            next() {
                this.index = (this.index + 1) % this.images.length;
            }
        }"
        x-init="$watch('open', value => document.documentElement.classList.toggle('overflow-hidden', value))"
        x-on:open-post-gallery.window="openAt($event.detail.index)"
        x-on:keydown.escape.window="if (open) close()"
        x-on:keydown.window="
            if (! open) return;
            if ($event.key === 'ArrowLeft') previous();
            if ($event.key === 'ArrowRight') next();
        "
        x-show="open"
        x-transition.opacity.duration.200ms
        x-cloak
        class="fixed inset-0 z-[90] flex items-center justify-center bg-black/90 p-3 backdrop-blur-sm sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-label="Galería de imágenes de {{ $title }}"
        data-post-gallery
    >
        <button
            type="button"
            class="absolute inset-0 cursor-default"
            x-on:click="close()"
            aria-label="Cerrar galería"
            tabindex="-1"
        ></button>

        <div class="relative flex h-full w-full max-w-7xl flex-col items-center justify-center">
            <div class="absolute inset-x-0 top-0 z-10 flex items-center justify-between p-2 text-white sm:p-3">
                <p class="rounded-full bg-black/55 px-4 py-2 text-sm font-semibold backdrop-blur">
                    <span x-text="index + 1"></span>
                    <span aria-hidden="true"> / </span>
                    <span x-text="images.length"></span>
                </p>

                <button
                    type="button"
                    x-ref="closeButton"
                    x-on:click="close()"
                    class="grid size-11 place-items-center rounded-full bg-black/55 text-white transition hover:bg-white hover:text-black focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                    aria-label="Cerrar galería"
                >
                    <x-heroicon-o-x-mark class="size-6" aria-hidden="true" />
                </button>
            </div>

            <img
                x-bind:src="images[index]"
                alt="{{ $title }}"
                class="max-h-[calc(100vh-9rem)] max-w-full select-none object-contain"
            >

            @if ($images->count() > 1)
                <button
                    type="button"
                    x-on:click="previous()"
                    class="absolute left-0 grid size-12 place-items-center rounded-full bg-black/60 text-white transition hover:bg-white hover:text-black focus:outline-none focus-visible:ring-2 focus-visible:ring-white sm:left-3 sm:size-14"
                    aria-label="Ver imagen anterior"
                >
                    <x-heroicon-o-chevron-left class="size-7" aria-hidden="true" />
                </button>

                <button
                    type="button"
                    x-on:click="next()"
                    class="absolute right-0 grid size-12 place-items-center rounded-full bg-black/60 text-white transition hover:bg-white hover:text-black focus:outline-none focus-visible:ring-2 focus-visible:ring-white sm:right-3 sm:size-14"
                    aria-label="Ver imagen siguiente"
                >
                    <x-heroicon-o-chevron-right class="size-7" aria-hidden="true" />
                </button>

                <div class="absolute inset-x-0 bottom-0 flex justify-center gap-3 p-2 sm:p-3">
                    <button
                        type="button"
                        x-on:click="previous()"
                        class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-[#222222] transition hover:bg-[#F3F4F6] focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-black"
                    >
                        <x-heroicon-o-chevron-left class="size-5" aria-hidden="true" />
                        Anterior
                    </button>

                    <button
                        type="button"
                        x-on:click="next()"
                        class="inline-flex items-center gap-2 rounded-full bg-[#E91E63] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#D81B60] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E91E63] focus-visible:ring-offset-2 focus-visible:ring-offset-black"
                    >
                        Siguiente
                        <x-heroicon-o-chevron-right class="size-5" aria-hidden="true" />
                    </button>
                </div>
            @endif
        </div>
    </div>
@endif
