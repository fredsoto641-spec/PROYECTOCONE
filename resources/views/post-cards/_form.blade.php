@php
    $postCard = $postCard ?? null;
    $method = $method ?? 'POST';
    $submitLabel = $submitLabel ?? 'Guardar card';
    $fieldsValue = old('fields', $postCard?->fields ?? []);
    $colorValue = old('color', $postCard?->color ?? '#E91E63');
    $fillBackgroundValue = old('fill_background', $postCard?->fill_background ?? false);
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-6"
    x-data="{
        color: @js($colorValue),
        fields: @js(array_values($fieldsValue)),
        addField() {
            this.fields.push({ key: '', value: '' });
        },
        removeField(index) {
            this.fields.splice(index, 1);
        },
    }"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="title" value="Título de la card" />
        <x-text-input
            id="title"
            name="title"
            type="text"
            class="mt-1 block w-full"
            :value="old('title', $postCard?->title)"
            required
            autofocus
            placeholder="Perfil, Atención, Servicios..."
        />
        <x-input-error class="mt-2" :messages="$errors->get('title')" />
    </div>

    <div>
        <x-input-label for="color" value="Color de la card" />
        <div class="mt-1 flex gap-2">
            <input
                type="color"
                id="color-picker"
                class="h-10 w-12 rounded-md border border-gray-300 bg-white p-1 cursor-pointer"
                x-model="color"
            >
            <x-text-input
                id="color"
                name="color"
                type="text"
                class="block w-full"
                x-model="color"
                required
                placeholder="#E91E63"
                pattern="^#[0-9A-Fa-f]{6}$"
            />
        </div>
        <p class="mt-1 text-sm text-gray-500">Selecciona un color para la barra superior de la card</p>
        <x-input-error class="mt-2" :messages="$errors->get('color')" />
    </div>

    <div>
        <label class="inline-flex items-center gap-3">
            <input type="hidden" name="fill_background" value="0">
            <input
                type="checkbox"
                name="fill_background"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                {{ $fillBackgroundValue ? 'checked' : '' }}
            >
            <span class="text-sm font-medium text-gray-700">Usar el color como fondo de toda la card</span>
        </label>
        <p class="mt-1 text-sm text-gray-500">Si está activo, la card pública se mostrará con el color elegido como fondo.</p>
        <x-input-error class="mt-2" :messages="$errors->get('fill_background')" />
    </div>

    <div>
        <x-input-label for="sort_order" value="Orden" />
        <x-text-input
            id="sort_order"
            name="sort_order"
            type="number"
            class="mt-1 block w-full"
            :value="old('sort_order', $postCard?->sort_order ?? 0)"
            required
            min="0"
        />
        <p class="mt-1 text-sm text-gray-500">Las cards se ordenarán de menor a mayor</p>
        <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
    </div>

    <div>
        <label class="inline-flex items-center gap-3">
            <input type="hidden" name="is_active" value="0">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                {{ old('is_active', $postCard?->is_active ?? true) ? 'checked' : '' }}
            >
            <span class="text-sm font-medium text-gray-700">Card activa (disponible para usar en posts)</span>
        </label>
        <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
    </div>

    <div>
        <div class="flex items-center justify-between gap-4">
            <x-input-label value="Campos predefinidos" />
            <button
                type="button"
                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                title="Agregar campo"
                aria-label="Agregar campo"
                x-on:click="addField()"
            >
                <x-heroicon-o-plus class="h-5 w-[18px]" aria-hidden="true" />
            </button>
        </div>

        <p class="mt-1 text-sm text-gray-500">Define las claves que esta card tendrá. Los usuarios solo necesitarán completar los valores.</p>

        <template x-if="fields.length === 0">
            <p class="mt-3 rounded-md border border-gray-200 bg-gray-50 p-4 text-center text-sm text-gray-500">
                No hay campos definidos. Agrega al menos una clave.
            </p>
        </template>

        <div class="mt-3 space-y-3">
            <template x-for="(field, index) in fields" :key="index">
                <div class="flex gap-3">
                    <div class="flex-1">
                        <x-text-input
                            type="text"
                            class="block w-full"
                            x-model="field.key"
                            x-bind:name="`fields[${index}][key]`"
                            placeholder="Edad, Altura, Idiomas..."
                        />
                    </div>
                    <div class="flex-1">
                        <x-text-input
                            type="text"
                            class="block w-full"
                            x-model="field.value"
                            x-bind:name="`fields[${index}][value]`"
                            placeholder="Valor por defecto (opcional)"
                        />
                    </div>
                    <button
                        type="button"
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                        title="Eliminar campo"
                        aria-label="Eliminar campo"
                        x-on:click="removeField(index)"
                    >
                        <x-heroicon-o-trash class="h-5 w-[18px]" aria-hidden="true" />
                    </button>
                </div>
            </template>
        </div>

        <x-input-error class="mt-2" :messages="$errors->get('fields')" />
    </div>

    <div class="flex items-center justify-end gap-4">
        <a
            href="{{ route('post-cards.index') }}"
            class="rounded-md px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
        >
            Cancelar
        </a>
        <x-primary-button>
            {{ $submitLabel }}
        </x-primary-button>
    </div>
</form>
