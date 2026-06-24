<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Plantillas de Cards
            </h2>
            @can('cards.create')
                <a
                href="{{ route('post-cards.create') }}"
                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                title="Crear card"
                aria-label="Crear card"
            >
                <x-heroicon-o-plus class="h-5 w-[18px]" aria-hidden="true" />
                <span class="sr-only">Crear card</span>
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
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Card</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Campos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Orden</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($postCards as $postCard)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-1.5 rounded-full" style="--card-color: {{ $postCard->color }}; background-color: var(--card-color);"></div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $postCard->title }}</div>
                                                <div class="mt-0.5 text-xs text-gray-500">{{ $postCard->color }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($postCard->fields ?? [] as $field)
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                                    {{ $field['key'] ?? '' }}
                                                </span>
                                            @empty
                                                <span class="text-sm text-gray-400">Sin campos</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $postCard->sort_order }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $postCard->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $postCard->is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            @can('cards.publish')
                                                <button
                                                type="button"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="{{ $postCard->is_active ? 'Desactivar' : 'Activar' }}"
                                                aria-label="{{ $postCard->is_active ? 'Desactivar card' : 'Activar card' }}"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'toggle-post-card-{{ $postCard->id }}')"
                                            >
                                                @if ($postCard->is_active)
                                                    <x-heroicon-o-eye-slash class="h-5 w-[18px]" aria-hidden="true" />
                                                @else
                                                    <x-heroicon-o-eye class="h-5 w-[18px]" aria-hidden="true" />
                                                @endif
                                                <span class="sr-only">{{ $postCard->is_active ? 'Desactivar card' : 'Activar card' }}</span>
                                                </button>
                                            @endcan

                                            @can('cards.edit')
                                                <a
                                                href="{{ route('post-cards.edit', $postCard) }}"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="Editar"
                                                aria-label="Editar card"
                                            >
                                                <x-heroicon-o-pencil-square class="h-5 w-[18px]" aria-hidden="true" />
                                                <span class="sr-only">Editar card</span>
                                                </a>
                                            @endcan

                                            @can('cards.delete')
                                                <button
                                                type="button"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="Eliminar"
                                                aria-label="Eliminar card"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'delete-post-card-{{ $postCard->id }}')"
                                            >
                                                <x-heroicon-o-trash class="h-5 w-[18px]" aria-hidden="true" />
                                                <span class="sr-only">Eliminar card</span>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                <!-- Toggle Modal -->
                                @can('cards.publish')
                                    <x-modal name="toggle-post-card-{{ $postCard->id }}" focusable>
                                    <form method="post" action="{{ route('post-cards.toggle-visibility', $postCard) }}" class="p-6">
                                        @csrf
                                        @method('PATCH')

                                        <h2 class="text-lg font-medium text-gray-900">
                                            ¿{{ $postCard->is_active ? 'Desactivar' : 'Activar' }} card "{{ $postCard->title }}"?
                                        </h2>

                                        <p class="mt-3 text-sm text-gray-600">
                                            @if ($postCard->is_active)
                                                La card no estará disponible para usar en nuevos posts.
                                            @else
                                                La card estará disponible para usar en posts.
                                            @endif
                                        </p>

                                        <div class="mt-6 flex justify-end gap-3">
                                            <x-secondary-button x-on:click="$dispatch('close')">
                                                Cancelar
                                            </x-secondary-button>
                                            <x-primary-button>
                                                {{ $postCard->is_active ? 'Desactivar' : 'Activar' }}
                                            </x-primary-button>
                                        </div>
                                    </form>
                                    </x-modal>
                                @endcan

                                <!-- Delete Modal -->
                                @can('cards.delete')
                                    <x-modal name="delete-post-card-{{ $postCard->id }}" focusable>
                                    <form method="post" action="{{ route('post-cards.destroy', $postCard) }}" class="p-6">
                                        @csrf
                                        @method('DELETE')

                                        <h2 class="text-lg font-medium text-gray-900">
                                            ¿Eliminar card "{{ $postCard->title }}"?
                                        </h2>

                                        <p class="mt-3 text-sm text-gray-600">
                                            Esta acción no se puede deshacer. La card ya no estará disponible para nuevos posts.
                                        </p>

                                        <div class="mt-6 flex justify-end gap-3">
                                            <x-secondary-button x-on:click="$dispatch('close')">
                                                Cancelar
                                            </x-secondary-button>
                                            <x-danger-button>
                                                Eliminar card
                                            </x-danger-button>
                                        </div>
                                    </form>
                                    </x-modal>
                                @endcan
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <x-heroicon-o-rectangle-stack class="h-12 w-12 text-gray-400" />
                                            <p class="text-sm font-medium text-gray-500">No hay cards creadas</p>
                                            @can('cards.create')
                                                <a
                                                href="{{ route('post-cards.create') }}"
                                                class="mt-2 inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            >
                                                <x-heroicon-o-plus class="h-5 w-5" />
                                                Crear primera card
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($postCards->hasPages())
                    <div class="border-t border-gray-200 bg-white px-4 py-3">
                        {{ $postCards->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
