<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Integraciones
            </h2>
            @can('integrations.create')
                <a
                href="{{ route('integrations.create') }}"
                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                title="Crear integración"
                aria-label="Crear integración"
            >
                <x-heroicon-o-plus class="h-5 w-[18px]" aria-hidden="true" />
                <span class="sr-only">Crear integración</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm font-medium text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Integración</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Proveedor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Base URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Botón</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($integrations as $integration)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $integration->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ \App\Models\Integration::PROVIDERS[$integration->provider] ?? $integration->provider }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $integration->base_url }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-semibold text-white" style="background-color: {{ $integration->button_color ?? '#222222' }};">
                                            <x-dynamic-component :component="$integration->icon ?: 'heroicon-o-link'" class="size-4" />
                                            {{ $integration->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $integration->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $integration->is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            @can('integrations.publish')
                                                <button
                                                type="button"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="{{ $integration->is_active ? 'Ocultar' : 'Publicar' }}"
                                                aria-label="{{ $integration->is_active ? 'Ocultar integración' : 'Publicar integración' }}"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'toggle-integration-{{ $integration->id }}')"
                                            >
                                                @if ($integration->is_active)
                                                    <x-heroicon-o-eye-slash class="h-5 w-[18px]" aria-hidden="true" />
                                                @else
                                                    <x-heroicon-o-eye class="h-5 w-[18px]" aria-hidden="true" />
                                                @endif
                                                <span class="sr-only">{{ $integration->is_active ? 'Ocultar integración' : 'Publicar integración' }}</span>
                                                </button>
                                            @endcan

                                            @can('integrations.edit')
                                                <a
                                                href="{{ route('integrations.edit', $integration) }}"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="Editar"
                                                aria-label="Editar integración"
                                            >
                                                <x-heroicon-o-pencil-square class="h-5 w-[18px]" aria-hidden="true" />
                                                <span class="sr-only">Editar integración</span>
                                                </a>
                                            @endcan

                                            @can('integrations.delete')
                                                <button
                                                type="button"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="Eliminar"
                                                aria-label="Eliminar integración"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'delete-integration-{{ $integration->id }}')"
                                            >
                                                <x-heroicon-o-trash class="h-5 w-[18px]" aria-hidden="true" />
                                                <span class="sr-only">Eliminar integración</span>
                                                </button>
                                            @endcan
                                        </div>

                                        @can('integrations.publish')
                                            <x-category-action-modal
                                            name="toggle-integration-{{ $integration->id }}"
                                            :title="$integration->is_active ? 'Ocultar integración' : 'Publicar integración'"
                                            :description="$integration->is_active ? 'Esta integración dejará de estar disponible para construir enlaces en posts.' : 'Esta integración volverá a estar disponible para construir enlaces en posts.'"
                                            :action="route('integrations.toggle-visibility', $integration)"
                                            method="PATCH"
                                            :confirm-label="$integration->is_active ? 'Ocultar' : 'Publicar'"
                                            />
                                        @endcan

                                        @can('integrations.delete')
                                            <x-category-action-modal
                                            name="delete-integration-{{ $integration->id }}"
                                            title="Eliminar integración"
                                            description="Esta acción eliminará la integración permanentemente."
                                            :action="route('integrations.destroy', $integration)"
                                            method="DELETE"
                                            confirm-label="Eliminar"
                                            variant="danger"
                                            />
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <x-heroicon-o-rectangle-stack class="h-12 w-12 text-gray-400" />
                                            <p class="text-sm font-medium text-gray-500">No hay integraciones creadas</p>
                                            @can('integrations.create')
                                                <a
                                                href="{{ route('integrations.create') }}"
                                                class="mt-2 inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            >
                                                <x-heroicon-o-plus class="h-5 w-5" />
                                                Crear primera integración
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $integrations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
