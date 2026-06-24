<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPostRelatedPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_at_most_three_publicly_visible_related_posts(): void
    {
        $category = Category::query()->create([
            'name' => 'Categoría',
            'slug' => 'categoria',
            'is_active' => true,
        ]);

        $currentPost = $this->createPost($category, 'Post actual', 'post-actual', [
            'cover_image_url' => 'https://example.com/cover.jpg',
            'gallery_image_urls' => [
                "https://example.com/cover.jpg\nhttps://example.com/gallery-2.jpg",
            ],
        ]);

        foreach (range(1, 4) as $number) {
            $this->createPost(
                $category,
                "Relacionado {$number}",
                "relacionado-{$number}",
                ['published_at' => now()->subMinutes($number)],
            );
        }

        $this->createPost($category, 'Inactivo', 'inactivo', ['is_active' => false]);
        $this->createPost($category, 'Pendiente', 'pendiente', ['published_at' => now()->addDay()]);
        $this->createPost($category, 'Finalizado', 'finalizado', ['ends_at' => now()->subDay()]);

        $response = $this->get(route('posts.public.show', [
            'category' => $category,
            'post' => $currentPost,
        ]));

        $response->assertOk();
        $response->assertSee('data-post-gallery', false);
        $response->assertSee('https://example.com/gallery-2.jpg', false);
        $response->assertSee('Galería');
        $response->assertSee('index: 1', false);
        $response->assertViewHas('relatedPosts', function ($relatedPosts): bool {
            $titles = $relatedPosts->pluck('title');

            return $relatedPosts->count() === 3
                && $titles->every(fn (string $title): bool => str_starts_with($title, 'Relacionado'));
        });
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createPost(Category $category, string $title, string $slug, array $attributes = []): Post
    {
        return Post::query()->create(array_merge([
            'category_id' => $category->id,
            'title' => $title,
            'slug' => $slug,
            'body' => 'Contenido',
            'is_active' => true,
            'published_at' => now()->subDay(),
        ], $attributes));
    }
}
