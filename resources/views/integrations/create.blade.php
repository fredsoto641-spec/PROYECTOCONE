<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear integración
            </h2>
            <a href="{{ route('integrations.index') }}" class="admin-button-cancel">
                Ver lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @include('integrations._form', [
                        'action' => route('integrations.store'),
                        'providers' => $providers,
                        'submitLabel' => 'Crear integración',
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
