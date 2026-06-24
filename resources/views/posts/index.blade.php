<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Posts
            </h2>
            @can('posts.create')
                <a
                href="{{ route('posts.create') }}"
                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                title="Crear post"
                aria-label="Crear post"
            >
                <x-heroicon-o-plus class="h-5 w-[18px]" aria-hidden="true" />
                <span class="sr-only">Crear post</span>
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
                <div class="overflow-hidden">
                    <table class="w-full table-fixed divide-y divide-gray-200">
                        <colgroup>
                            <col style="width: 31%;">
                            <col style="width: 12%;">
                            <col style="width: 11%;">
                            <col style="width: 14%;">
                            <col style="width: 11%;">
                            <col style="width: 21%;">
                        </colgroup>
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Post</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoría</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Contactos</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tags</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($posts as $post)
                                <tr>
                                    <td class="max-w-0 px-4 py-4">
                                        <div class="flex w-full min-w-0 items-center gap-3 overflow-hidden">
                                            <div class="h-12 w-16 shrink-0 overflow-hidden rounded-md bg-gray-100">
                                                @if ($post->cover_image_url)
                                                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-full w-full object-cover">
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1 overflow-hidden">
                                                <div class="truncate font-medium text-gray-900">{{ Str::limit($post->title, 32) }}</div>
                                                @if ($post->subtitle)
                                                    <div class="mt-1 truncate text-sm text-gray-500">{{ Str::limit($post->subtitle, 64) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        <div class="truncate">{{ $post->category?->name }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-2 text-gray-700">
                                            @if ($post->whatsapp_url)
                                                <span title="WhatsApp" aria-label="WhatsApp">
                                                    <x-heroicon-o-chat-bubble-left-right class="size-4" aria-hidden="true" />
                                                </span>
                                            @endif
                                            @if ($post->telegram_url)
                                                <span title="Telegram" aria-label="Telegram">
                                                    <x-heroicon-o-paper-airplane class="size-4" aria-hidden="true" />
                                                </span>
                                            @endif
                                            @if ($post->sms_url)
                                                <span title="SMS" aria-label="SMS">
                                                    <x-heroicon-o-device-phone-mobile class="size-4" aria-hidden="true" />
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach (array_slice($post->tags ?? [], 0, 3) as $tag)
                                                <span class="max-w-full truncate rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($post->isFinished())
                                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">
                                                Finalizado
                                            </span>
                                        @elseif (! $post->is_active)
                                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                                Oculto
                                            </span>
                                        @elseif ($post->isPendingPublication())
                                            <span class="inline-flex rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-semibold text-yellow-800">
                                                Pendiente
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">
                                                Publicado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-1.5">
                                            @can('posts.publish')
                                                <button
                                                type="button"
                                                class="inline-flex size-9 items-center justify-center rounded-md border transition focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 {{ $post->is_vip ? 'text-white' : 'border-gray-300 text-gray-900 hover:bg-gray-50' }}"
                                                @if ($post->is_vip)
                                                    style="border-color: #d97706; background-color: #d97706;"
                                                    onmouseover="this.style.backgroundColor='#b45309'; this.style.borderColor='#b45309'"
                                                    onmouseout="this.style.backgroundColor='#d97706'; this.style.borderColor='#d97706'"
                                                @endif
                                                title="{{ $post->is_vip ? 'Quitar VIP' : 'Marcar VIP' }}"
                                                aria-label="{{ $post->is_vip ? 'Quitar VIP del post' : 'Marcar post como VIP' }}"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'toggle-vip-post-{{ $post->id }}')"
                                            >
                                                @if ($post->is_vip)
                                                    <x-heroicon-s-star class="h-5 w-[18px]" aria-hidden="true" />
                                                @else
                                                    <x-heroicon-o-star class="h-5 w-[18px]" aria-hidden="true" />
                                                @endif
                                                <span class="sr-only">{{ $post->is_vip ? 'Quitar VIP del post' : 'Marcar post como VIP' }}</span>
                                                </button>

                                                <button
                                                type="button"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="{{ $post->is_active ? 'Ocultar' : 'Publicar' }}"
                                                aria-label="{{ $post->is_active ? 'Ocultar post' : 'Publicar post' }}"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'toggle-post-{{ $post->id }}')"
                                            >
                                                @if ($post->is_active)
                                                    <x-heroicon-o-eye-slash class="h-5 w-[18px]" aria-hidden="true" />
                                                @else
                                                    <x-heroicon-o-eye class="h-5 w-[18px]" aria-hidden="true" />
                                                @endif
                                                <span class="sr-only">{{ $post->is_active ? 'Ocultar post' : 'Publicar post' }}</span>
                                                </button>
                                            @endcan

                                            @can('posts.edit')
                                                <a
                                                href="{{ route('posts.edit', $post) }}"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="Editar"
                                                aria-label="Editar post"
                                            >
                                                <x-heroicon-o-pencil-square class="h-5 w-[18px]" aria-hidden="true" />
                                                <span class="sr-only">Editar post</span>
                                                </a>
                                            @endcan

                                            @can('posts.delete')
                                                <button
                                                type="button"
                                                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                                title="Eliminar"
                                                aria-label="Eliminar post"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'delete-post-{{ $post->id }}')"
                                            >
                                                <x-heroicon-o-trash class="h-5 w-[18px]" aria-hidden="true" />
                                                <span class="sr-only">Eliminar post</span>
                                                </button>
                                            @endcan
                                        </div>

                                        @can('posts.publish')
                                            <x-category-action-modal
                                            name="toggle-vip-post-{{ $post->id }}"
                                            :title="$post->is_vip ? 'Quitar VIP' : 'Marcar VIP'"
                                            :description="$post->is_vip ? 'Este post dejará de mostrarse como VIP.' : 'Este post será marcado como VIP.'"
                                            :action="route('posts.toggle-vip', $post)"
                                            method="PATCH"
                                            :confirm-label="$post->is_vip ? 'Quitar VIP' : 'Marcar VIP'"
                                            />

                                            <x-category-action-modal
                                            name="toggle-post-{{ $post->id }}"
                                            :title="$post->is_active ? 'Ocultar post' : 'Publicar post'"
                                            :description="$post->is_active ? 'Este post dejará de mostrarse públicamente.' : 'Este post volverá a mostrarse públicamente.'"
                                            :action="route('posts.toggle-visibility', $post)"
                                            method="PATCH"
                                            :confirm-label="$post->is_active ? 'Ocultar' : 'Publicar'"
                                            />
                                        @endcan

                                        @can('posts.delete')
                                            <x-category-action-modal
                                            name="delete-post-{{ $post->id }}"
                                            title="Eliminar post"
                                            description="Esta acción eliminará el post permanentemente."
                                            :action="route('posts.destroy', $post)"
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
                                            <p class="text-sm font-medium text-gray-500">No hay posts creados</p>
                                            @can('posts.create')
                                                <a
                                                href="{{ route('posts.create') }}"
                                                class="mt-2 inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            >
                                                <x-heroicon-o-plus class="h-5 w-5" />
                                                Crear primer post
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
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
