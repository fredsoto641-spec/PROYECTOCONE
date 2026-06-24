<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Str;

class PublicSearchOptions
{
    /**
     * @return array<int, array{label: string, value: string}>
     */
    public static function categories(): array
    {
        return Category::query()
            ->where('is_active', true)
            ->whereHas('posts', fn ($query) => $query->publiclyVisible())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['name', 'slug'])
            ->map(fn (Category $category): array => [
                'label' => $category->name,
                'value' => $category->slug,
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public static function locations(): array
    {
        return Post::query()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->publiclyVisible()
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->get(['location'])
            ->groupBy(fn (Post $post): string => Str::slug($post->location))
            ->filter(fn ($posts, string $slug): bool => $slug !== '' && $posts->isNotEmpty())
            ->map(fn ($posts, string $slug): array => [
                'label' => trim($posts->first()->location),
                'value' => $slug,
            ])
            ->sortBy(fn (array $location): string => Str::lower($location['label']))
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     categories: array<int, array{label: string, value: string}>,
     *     locations: array<int, array{label: string, value: string}>
     * }
     */
    public static function all(): array
    {
        return [
            'categories' => self::categories(),
            'locations' => self::locations(),
        ];
    }
}
