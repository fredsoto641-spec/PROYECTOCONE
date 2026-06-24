<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Support\Str;

class PublicLocationDirectory
{
    /**
     * @return array{
     *     eyebrow: string,
     *     title: string,
     *     description: string,
     *     groups: array<int, array{
     *         title: string,
     *         description: string,
     *         links: array<int, array{label: string, href: string}>
     *     }>
     * }
     */
    public static function make(): array
    {
        $locations = Post::query()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->publiclyVisible()
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->get(['id', 'location'])
            ->groupBy(fn (Post $post): string => Str::slug($post->location))
            ->filter(fn ($posts, string $slug): bool => $slug !== '' && $posts->isNotEmpty())
            ->map(fn ($posts, string $slug): array => [
                'label' => trim($posts->first()->location),
                'href' => route('posts.locations.show', ['location' => $slug]),
                'count' => $posts->count(),
            ])
            ->sort(function (array $first, array $second): int {
                return $second['count'] <=> $first['count']
                    ?: Str::lower($first['label']) <=> Str::lower($second['label']);
            })
            ->values();

        $groupLinks = [[], [], []];

        foreach ($locations as $index => $location) {
            $groupLinks[$index % 3][] = [
                'label' => $location['label'],
                'href' => $location['href'],
            ];
        }

        $groupTitles = [
            'Zonas con actividad',
            'Más ubicaciones',
            'Otros lugares',
        ];

        return [
            'eyebrow' => 'Directorio',
            'title' => 'Explora anuncios por zona',
            'description' => 'Accesos rápidos a las ubicaciones que actualmente tienen publicaciones activas.',
            'groups' => collect($groupLinks)
                ->map(fn (array $links, int $index): array => [
                    'title' => $groupTitles[$index],
                    'description' => trans_choice(
                        '{0} Sin ubicaciones activas|{1} 1 ubicación activa|[2,*] :count ubicaciones activas',
                        count($links),
                        ['count' => count($links)],
                    ),
                    'links' => $links,
                ])
                ->all(),
        ];
    }
}
