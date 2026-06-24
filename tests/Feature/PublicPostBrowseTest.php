<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPostBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_can_be_browsed_by_location_and_tag(): void
    {
        $category = Category::query()->create([
            'name' => 'Categoría',
            'slug' => 'categoria',
            'is_active' => true,
        ]);

        Post::query()->create([
            'category_id' => $category->id,
            'title' => 'Post visible',
            'subtitle' => 'Este subtítulo debe aparecer debajo del título',
            'slug' => 'post-visible',
            'body' => 'Contenido',
            'location' => 'José Leonardo Ortiz',
            'tags' => ['Verificado', 'Lima Norte'],
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        Post::query()->create([
            'category_id' => $category->id,
            'title' => 'Post inactivo',
            'slug' => 'post-inactivo',
            'body' => 'Contenido',
            'location' => 'José Leonardo Ortiz',
            'tags' => ['Lima Norte'],
            'is_active' => false,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/u')
            ->assertOk()
            ->assertSee('/u/jose-leonardo-ortiz', false);

        $this->get('/u/jose-leonardo-ortiz')
            ->assertOk()
            ->assertSee('Post visible')
            ->assertSee('Este subtítulo debe aparecer debajo del título')
            ->assertSee('class="truncate text-lg', false)
            ->assertSee('class="mt-1 h-5 truncate', false)
            ->assertDontSee('Post inactivo');

        $this->get('/t')
            ->assertOk()
            ->assertSee('/t/lima-norte', false);

        $this->get('/t/lima-norte')
            ->assertOk()
            ->assertSee('Post visible')
            ->assertDontSee('Post inactivo');
    }

    public function test_category_location_and_tag_lists_are_paginated_by_twenty_posts(): void
    {
        $category = Category::query()->create([
            'name' => 'Categoría paginada',
            'slug' => 'categoria-paginada',
            'is_active' => true,
        ]);

        foreach (range(1, 21) as $number) {
            Post::query()->create([
                'category_id' => $category->id,
                'title' => "Post paginado {$number}",
                'slug' => "post-paginado-{$number}",
                'body' => 'Contenido',
                'location' => 'Lima',
                'tags' => ['Promoción'],
                'is_active' => true,
                'published_at' => now()->subMinutes($number),
            ]);
        }

        foreach ([
            route('categories.public.show', ['category' => $category]),
            route('posts.locations.show', ['location' => 'lima']),
            route('posts.tags.show', ['tag' => 'promocion']),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertViewHas('posts', fn ($posts): bool => $posts->total() === 21
                    && $posts->perPage() === Post::PUBLIC_PER_PAGE
                    && $posts->count() === 20
                    && $posts->hasPages());

            $this->get($url.'?page=2')
                ->assertOk()
                ->assertViewHas('posts', fn ($posts): bool => $posts->total() === 21
                    && $posts->currentPage() === 2
                    && $posts->count() === 1);
        }
    }
}
