@php
    $integration = $integration ?? null;
    $method = $method ?? 'POST';
    $submitLabel = $submitLabel ?? 'Guardar integración';
    $credentialsValue = old('credentials', $integration?->credentials ? json_encode($integration->credentials, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '');
    $defaultIcon = \App\Models\Integration::DEFAULT_ICONS[old('provider', $integration?->provider ?? 'custom')] ?? \App\Models\Integration::DEFAULT_ICONS['custom'];
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="name" value="Nombre" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $integration?->name)"
                required
                autofocus
            />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="provider" value="Proveedor" />
            <select
                id="provider"
                name="provider"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
            >
                <option value="">Selecciona un proveedor</option>
                @foreach ($providers as $provider => $label)
                    <option value="{{ $provider }}" @selected(old('provider', $integration?->provider) === $provider)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('provider')" />
        </div>
    </div>

    <div>
        <x-input-label for="base_url" value="Base URL" />
            <x-text-input
                id="base_url"
                name="base_url"
                type="text"
                class="mt-1 block w-full"
                :value="old('base_url', $integration?->base_url)"
                placeholder="https://wa.me"
        />
        <x-input-error class="mt-2" :messages="$errors->get('base_url')" />
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="button_color" value="Color del botón" />
            <div class="mt-1 flex gap-2">
                <input
                    id="button_color_picker"
                    type="color"
                    class="h-10 w-12 rounded-md border border-gray-300 bg-white p-1"
                    value="{{ old('button_color', $integration?->button_color ?? '#222222') }}"
                    onchange="this.nextElementSibling.value = this.value"
                >
                <x-text-input
                    id="button_color"
                    name="button_color"
                    type="text"
                    class="block w-full"
                    :value="old('button_color', $integration?->button_color ?? '#222222')"
                    required
                />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('button_color')" />
        </div>

        <div>
            <x-input-label for="icon" value="Icono Heroicon" />
            <select
                id="icon"
                name="icon"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                @foreach (\App\Models\Integration::ICON_OPTIONS as $icon => $label)
                    <option value="{{ $icon }}" @selected(old('icon', $integration?->icon ?? $defaultIcon) === $icon)>
                        {{ $label }} · {{ $icon }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('icon')" />
        </div>
    </div>

    <div>
        <x-input-label for="credentials" value="Credenciales JSON" />
        <textarea
            id="credentials"
            name="credentials"
            rows="6"
            class="mt-1 block w-full font-mono text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder='{"api_key": "..."}'
        >{{ $credentialsValue }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('credentials')" />
    </div>

    <div class="flex items-end">
        <label for="is_active" class="inline-flex items-center gap-3 rounded-md border border-gray-200 px-4 py-3">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('is_active', $integration?->is_active ?? true))
            >
            <span class="text-sm font-medium text-gray-700">Activa</span>
        </label>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('integrations.index') }}" class="admin-button-cancel">
            Cancelar
        </a>
        <x-primary-button>
            {{ $submitLabel }}
        </x-primary-button>
    </div>
</form>
