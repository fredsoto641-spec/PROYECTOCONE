<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Card
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @include('post-cards._form', [
                        'action' => route('post-cards.store'),
                        'method' => 'POST',
                        'submitLabel' => 'Crear card',
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
