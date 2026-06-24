<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Support\PublicLocationDirectory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLocationDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_three_clickable_groups_using_only_public_post_locations(): void
    {
        $activeCategory = Category::query()->create([
            'name' => 'Activa',
            'slug' => 'activa',
            'is_active' => true,
        ]);
        $inactiveCategory = Category::query()->create([
            'name' => 'Inactiva',
            'slug' => 'inactiva',
            'is_active' => false,
        ]);

        foreach (['Miraflores', 'San Isidro', 'Barranco', 'Lima'] as $index => $location) {
            $this->createPost($activeCategory, "Visible {$index}", "visible-{$index}", $location);
        }

        $this->createPost($activeCategory, 'Oculto', 'oculto', 'Comas', false);
        $this->createPost($inactiveCategory, 'Categoría oculta', 'categoria-oculta', 'Surco');

        $directory = PublicLocationDirectory::make();
        $links = collect($directory['groups'])->pluck('links')->flatten(1);

        $this->assertCount(3, $directory['groups']);
        $this->assertEqualsCanonicalizing(
            ['Miraflores', 'San Isidro', 'Barranco', 'Lima'],
            $links->pluck('label')->all(),
        );
        $this->assertTrue($links->every(fn (array $link): bool => str_contains($link['href'], '/u/')));
        $this->assertNotContains('Comas', $links->pluck('label'));
        $this->assertNotContains('Surco', $links->pluck('label'));
    }

    private function createPost(Category $category, string $title, string $slug, string $location, bool $active = true): Post
    {
        return Post::query()->create([
            'category_id' => $category->id,
            'title' => $title,
            'slug' => $slug,
            'body' => 'Contenido',
            'location' => $location,
            'is_active' => $active,
            'published_at' => now()->subDay(),
        ]);
    }
}
