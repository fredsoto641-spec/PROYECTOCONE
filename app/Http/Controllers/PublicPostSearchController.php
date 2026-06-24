<?php

namespace App\Http\Controllers;

use App\Models\AgeGateSetting;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Support\PublicSearchOptions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPostSearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'query' => ['nullable', 'string', 'max:255'],
        ]);

        $options = PublicSearchOptions::all();
        $locationSlug = trim((string) ($filters['location'] ?? ''));
        $categorySlug = trim((string) ($filters['category'] ?? ''));
        $keyword = trim((string) ($filters['query'] ?? ''));
        $location = collect($options['locations'])->firstWhere('value', $locationSlug);
        $siteSettings = SiteSetting::current();

        $query = Post::query()
            ->with('category')
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->publiclyVisible()
            ->when($categorySlug !== '', fn ($query) => $query
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug)))
            ->when($locationSlug !== '', function ($query) use ($location): void {
                if ($location) {
                    $query->where('location', $location['label']);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $like = '%'.$keyword.'%';

                $query->where(function ($keywordQuery) use ($like): void {
                    $keywordQuery
                        ->where('title', 'like', $like)
                        ->orWhere('subtitle', 'like', $like)
                        ->orWhere('body', 'like', $like)
                        ->orWhere('tags', 'like', $like);
                });
            })
            ->latest('published_at')
            ->latest('created_at');

        $posts = $query
            ->paginate(Post::PUBLIC_PER_PAGE)
            ->withQueryString()
            ->through(fn (Post $post): array => $this->listing($post, $siteSettings));

        return view('posts.search-public', [
            'ageGate' => AgeGateSetting::current()->toModalContent(),
            'filters' => [
                'category' => $categorySlug,
                'location' => $locationSlug,
                'query' => $keyword,
            ],
            'posts' => $posts,
            'searchCategories' => $options['categories'],
            'searchLocations' => $options['locations'],
            'siteSettings' => $siteSettings,
        ]);
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
