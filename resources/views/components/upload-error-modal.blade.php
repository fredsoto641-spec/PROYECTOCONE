@php
    $details = session('upload_error_modal');
    $technical = is_array($details['technical'] ?? null) ? $details['technical'] : [];
@endphp

@if (is_array($details))
    <x-modal name="upload-error-details" :show="true" maxWidth="2xl" focusable>
        <div class="p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <span class="grid size-12 shrink-0 place-items-center rounded-full bg-red-100 text-red-700">
                    <x-heroicon-o-exclamation-triangle class="size-6" aria-hidden="true" />
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold uppercase tracking-wide text-red-700">No pudimos cargar la imagen</p>
                    <h2 class="mt-1 text-xl font-bold text-gray-900">{{ $details['summary'] }}</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">{{ $details['suggestion'] }}</p>
                </div>
            </div>

            <details class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-gray-800">
                    Detalles técnicos para sistemas
                </summary>
                <dl class="grid gap-px border-t border-gray-200 bg-gray-200 sm:grid-cols-2">
                    @foreach ($technical as $label => $value)
                        <div class="bg-white px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ str_replace('_', ' ', $label) }}
                            </dt>
                            <dd class="mt-1 break-words font-mono text-xs text-gray-800">
                                {{ $value === null || $value === '' ? '—' : $value }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </details>

            <div class="mt-6 flex justify-end">
                <button
                    type="button"
                    class="admin-button-primary"
                    x-on:click="$dispatch('close-modal', 'upload-error-details')"
                >
                    Entendido
                </button>
            </div>
        </div>
    </x-modal>
@endif
