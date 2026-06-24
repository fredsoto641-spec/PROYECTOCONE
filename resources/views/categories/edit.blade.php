<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar categoría
            </h2>
            <a href="{{ route('categories.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                Ver lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @include('categories._form', [
                        'category' => $category,
                        'action' => route('categories.update', $category),
                        'method' => 'PUT',
                        'submitLabel' => 'Actualizar categoría',
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
