<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear post
            </h2>
            <a href="{{ route('posts.index') }}" class="admin-button-cancel">
                Ver lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @include('posts._form', [
                'action' => route('posts.store'),
                'categories' => $categories,
                'integrations' => $integrations,
                'locations' => $locations,
                'submitLabel' => 'Crear post',
            ])
        </div>
    </div>
</x-app-layout>
