@php
    $category = $category ?? null;
    $method = $method ?? 'POST';
    $submitLabel = $submitLabel ?? 'Guardar';
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-1 block w-full"
            :value="old('name', $category?->name)"
            required
            autofocus
        />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="slug" value="Slug" />
        <x-text-input
            id="slug"
            name="slug"
            type="text"
            class="mt-1 block w-full"
            :value="old('slug', $category?->slug)"
            placeholder="Se genera automáticamente si lo dejas vacío"
        />
        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
    </div>

    <div>
        <x-input-label for="description" value="Descripción" />
        <textarea
            id="description"
            name="description"
            rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >{{ old('description', $category?->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="rounded-lg border border-gray-200 p-4">
        <h3 class="text-sm font-semibold text-gray-900">Imagen de categoría</h3>
        <p class="mt-1 text-sm text-gray-500">Puedes conservar una URL o subir una imagen. Si completas ambas, el archivo subido tendrá prioridad.</p>

        <div class="mt-4 grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="image_url" value="URL de imagen" />
                <x-text-input
                    id="image_url"
                    name="image_url"
                    type="url"
                    class="mt-1 block w-full"
                    :value="old('image_url', $category?->image_url)"
                    placeholder="https://..."
                />
                <x-input-error class="mt-2" :messages="$errors->get('image_url')" />
            </div>

            <div>
                <x-input-label for="image_file" value="Subir imagen" />
                <input
                    id="image_file"
                    name="image_file"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:font-semibold file:text-gray-700 hover:file:bg-gray-200"
                >
                <p class="mt-2 text-xs text-gray-500">JPEG, PNG o WebP. Máximo actual del servidor: {{ app(\App\Support\SecureImageUploader::class)->effectiveMaxMegabytes() }} MB.</p>
                <x-input-error class="mt-2" :messages="$errors->get('image_file')" />
            </div>
        </div>

        @if ($category?->image_url)
            <img src="{{ $category->image_url }}" alt="Imagen actual de {{ $category->name }}" class="mt-4 h-28 w-44 rounded-lg object-cover">
        @endif
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="sort_order" value="Orden" />
            <x-text-input
                id="sort_order"
                name="sort_order"
                type="number"
                min="0"
                class="mt-1 block w-full"
                :value="old('sort_order', $category?->sort_order ?? 0)"
                required
            />
            <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
        </div>

        <div class="flex items-end">
            <label for="is_active" class="inline-flex items-center gap-3 rounded-md border border-gray-200 px-4 py-3">
                <input
                    id="is_active"
                    name="is_active"
                    type="checkbox"
                    value="1"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    @checked(old('is_active', $category?->is_active ?? true))
                >
                <span class="text-sm font-medium text-gray-700">Activa en portada</span>
            </label>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('categories.index') }}" class="admin-button-cancel">
            Cancelar
        </a>
        <x-primary-button>
            {{ $submitLabel }}
        </x-primary-button>
    </div>
</form>
