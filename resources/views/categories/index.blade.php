<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Categorías
            </h2>
            @can('categories.create')
                <a
                href="{{ route('categories.create') }}"
                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                title="Crear categoría"
                aria-label="Crear categoría"
            >
                <x-heroicon-o-plus class="h-5 w-[18px]" aria-hidden="true" />
                <span class="sr-only">Crear categoría</span>
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
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoría</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Slug</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Orden</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="h-14 w-20 overflow-hidden rounded-md bg-gray-100">
                                                @if ($category->image_url)
                                                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $category->name }}</div>
                                                @if ($category->description)
                                                    <div class="mt-1 max-w-md truncate text-sm text-gray-500">{{ $category->description }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $category->slug }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $category->sort_order }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $category->is_active ? 'Activa' : 'Oculta' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            @can('categories.publish')
                                                <button
                                                type="button"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="{{ $category->is_active ? 'Ocultar' : 'Publicar' }}"
                                                aria-label="{{ $category->is_active ? 'Ocultar categoría' : 'Publicar categoría' }}"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'toggle-category-{{ $category->id }}')"
                                            >
                                                @if ($category->is_active)
                                                    <x-heroicon-o-eye-slash class="h-5 w-[18px]" aria-hidden="true" />
                                                @else
                                                    <x-heroicon-o-eye class="h-5 w-[18px]" aria-hidden="true" />
                                                @endif
                                                <span class="sr-only">{{ $category->is_active ? 'Ocultar categoría' : 'Publicar categoría' }}</span>
                                                </button>
                                            @endcan

                                            @can('categories.edit')
                                                <a
                                                href="{{ route('categories.edit', $category) }}"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="Editar"
                                                aria-label="Editar categoría"
                                            >
                                                <x-heroicon-o-pencil-square class="h-5 w-[18px]" aria-hidden="true" />
                                                <span class="sr-only">Editar categoría</span>
                                                </a>
                                            @endcan

                                            @can('categories.delete')
                                                <button
                                                type="button"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="Eliminar"
                                                aria-label="Eliminar categoría"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'delete-category-{{ $category->id }}')"
                                            >
                                                <x-heroicon-o-trash class="h-5 w-[18px]" aria-hidden="true" />
                                                <span class="sr-only">Eliminar categoría</span>
                                                </button>
                                            @endcan
                                        </div>

                                        @can('categories.publish')
                                            <x-category-action-modal
                                            name="toggle-category-{{ $category->id }}"
                                            :title="$category->is_active ? 'Ocultar categoría' : 'Publicar categoría'"
                                            :description="$category->is_active ? 'Esta categoría dejará de mostrarse en la portada y en los filtros públicos.' : 'Esta categoría volverá a mostrarse en la portada y en los filtros públicos.'"
                                            :action="route('categories.toggle-visibility', $category)"
                                            method="PATCH"
                                            :confirm-label="$category->is_active ? 'Ocultar' : 'Publicar'"
                                            :variant="$category->is_active ? 'primary' : 'success'"
                                            />
                                        @endcan

                                        @can('categories.delete')
                                            <x-category-action-modal
                                            name="delete-category-{{ $category->id }}"
                                            title="Eliminar categoría"
                                            description="Esta acción eliminará la categoría permanentemente."
                                            :action="route('categories.destroy', $category)"
                                            method="DELETE"
                                            confirm-label="Eliminar"
                                            variant="danger"
                                            />
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                        Todavía no hay categorías creadas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
