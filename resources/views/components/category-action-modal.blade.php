@props([
    'name',
    'title',
    'description',
    'action',
    'method' => 'POST',
    'confirmLabel',
    'variant' => 'primary',
])

@php
    $cancelClasses = $variant === 'danger' ? 'admin-button-danger-outline sm:min-w-32' : 'admin-button-cancel';
@endphp

<x-modal :name="$name" maxWidth="md" focusable>
    <form method="POST" action="{{ $action }}" class="bg-white p-6">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="flex items-center justify-between gap-4">
            <h2 class="text-sm font-medium text-gray-900">
                {{ $title }}
            </h2>

            <button
                type="button"
                class="-mr-2 -mt-2 inline-flex size-8 items-center justify-center rounded-md border border-blue-300 text-gray-400 transition hover:bg-blue-50 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300"
                aria-label="Cerrar"
                x-on:click="$dispatch('close-modal', '{{ $name }}')"
            >
                <x-heroicon-o-x-mark class="size-6" aria-hidden="true" />
            </button>
        </div>

        <p class="mt-7 text-center text-sm leading-6 text-gray-800">
            {{ $description }}
        </p>

        <div class="mt-7 flex justify-end gap-3">
            <button
                type="button"
                class="{{ $cancelClasses }}"
                x-on:click="$dispatch('close-modal', '{{ $name }}')"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="admin-button-primary sm:min-w-36"
            >
                {{ $confirmLabel }}
            </button>
        </div>
    </form>
</x-modal>
