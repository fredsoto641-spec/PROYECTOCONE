<?php

namespace App\Http\Controllers;

use App\Models\AgeGateSetting;
use App\Models\Post;
use App\Models\SiteSetting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicPostBrowseController extends Controller
{
    public function locations(): View
    {
        return $this->directoryView(
            type: 'location',
            eyebrow: 'Directorio por ubicación',
            title: 'Explora posts por ubicación',
            description: 'Encuentra publicaciones activas agrupadas por ciudad, distrito o zona.',
        );
    }

    public function location(string $location): View
    {
        return $this->postsView(
            type: 'location',
            slug: $location,
            eyebrow: 'Ubicación',
            emptyMessage: 'No hay posts activos en esta ubicación.',
        );
    }

    public function tags(): View
    {
        return $this->directoryView(
            type: 'tag',
            eyebrow: 'Directorio por etiqueta',
            title: 'Explora posts por etiqueta',
            description: 'Descubre publicaciones activas agrupadas por sus etiquetas.',
        );
    }

    public function tag(string $tag): View
    {
        return $this->postsView(
            type: 'tag',
            slug: $tag,
            eyebrow: 'Etiqueta',
            emptyMessage: 'No hay posts activos con esta etiqueta.',
        );
    }

    private function directoryView(string $type, string $eyebrow, string $title, string $description): View
    {
        $groups = $this->publicPosts()
            ->flatMap(function (Post $post) use ($type): array {
                $values = $type === 'location'
                    ? [$post->location]
                    : ($post->tags ?? []);

                return collect($values)
                    ->filter(fn ($value): bool => filled($value))
                    ->map(fn ($value): array => [
                        'label' => trim((string) $value),
                        'post_id' => $post->id,
                    ])
                    ->all();
            })
            ->groupBy(fn (array $item): string => Str::slug($item['label']))
            ->filter(fn (Collection $items, string $slug): bool => $slug !== '' && $items->isNotEmpty())
            ->map(function (Collection $items, string $slug) use ($type): array {
                $route = $type === 'location' ? 'posts.locations.show' : 'posts.tags.show';

                return [
                    'label' => $items->first()['label'],
                    'slug' => $slug,
                    'count' => $items->pluck('post_id')->unique()->count(),
                    'href' => route($route, [$type === 'location' ? 'location' : 'tag' => $slug]),
                ];
            })
            ->sortBy(fn (array $group): string => Str::lower($group['label']))
            ->values();

        return view('posts.browse-directory', [
            'ageGate' => AgeGateSetting::current()->toModalContent(),
            'description' => $description,
            'eyebrow' => $eyebrow,
            'groups' => $groups,
            'siteSettings' => SiteSetting::current(),
            'title' => $title,
            'type' => $type,
        ]);
    }

    private function postsView(string $type, string $slug, string $eyebrow, string $emptyMessage): View
    {
        $matchingPosts = $this->publicPosts()
            ->filter(function (Post $post) use ($type, $slug): bool {
                $values = $type === 'location'
                    ? [$post->location]
                    : ($post->tags ?? []);

                return collect($values)
                    ->filter(fn ($value): bool => filled($value))
                    ->contains(fn ($value): bool => Str::slug((string) $value) === $slug);
            })
            ->values();

        abort_if($matchingPosts->isEmpty(), 404);

        $label = $type === 'location'
            ? trim((string) $matchingPosts->first()->location)
            : collect($matchingPosts->first()->tags ?? [])
                ->first(fn ($tag): bool => Str::slug((string) $tag) === $slug);

        $siteSettings = SiteSetting::current();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $posts = new LengthAwarePaginator(
            $matchingPosts
                ->forPage($page, Post::PUBLIC_PER_PAGE)
                ->map(fn (Post $post): array => $this->listing($post, $siteSettings))
                ->values(),
            $matchingPosts->count(),
            Post::PUBLIC_PER_PAGE,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );

        return view('posts.browse-public', [
            'ageGate' => AgeGateSetting::current()->toModalContent(),
            'directoryHref' => route($type === 'location' ? 'posts.locations.index' : 'posts.tags.index'),
            'emptyMessage' => $emptyMessage,
            'eyebrow' => $eyebrow,
            'label' => $label,
            'posts' => $posts,
            'siteSettings' => $siteSettings,
            'type' => $type,
        ]);
    }

    /**
     * @return Collection<int, Post>
     */
    private function publicPosts(): Collection
    {
        return Post::query()
            ->with('category')
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->publiclyVisible()
            ->latest('published_at')
            ->latest('created_at')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function listing(Post $post, SiteSetting $siteSettings): array
    {
        $tags = collect($post->tags ?? [])->map(fn (string $tag): string => mb_strtolower($tag));
        $category = $post->category;
        $coverImage = collect(preg_split('/\R/u', (string) $post->cover_image_url) ?: [])
            ->map(fn (string $url): string => trim($url))
            ->first(fn (string $url): bool => $url !== '');

        return [
            'id' => 'post-'.$post->id,
            'title' => $post->title,
            'subtitle' => $post->subtitle,
            'city' => $post->location ?: $siteSettings->server_country,
            'category' => $category?->name ?? 'General',
            'updated' => $post->published_at ? 'Publicado '.$post->published_at->diffForHumans() : 'Publicado recientemente',
            'price' => null,
            'image' => $coverImage ?: 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
            'profileHref' => $category && $post->slug
                ? route('posts.public.show', ['category' => $category->slug, 'post' => $post->slug])
                : '#',
            'verified' => $tags->contains('verificado'),
            'featured' => $post->is_vip || $tags->contains('destacado'),
        ];
    }
}
