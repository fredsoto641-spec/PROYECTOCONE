<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Integration;
use App\Models\Post;
use App\Models\PostCard;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_complete_demo_content_without_duplicates(): void
    {
        $this->seed(DemoContentSeeder::class);
        $this->seed(DemoContentSeeder::class);

        $this->assertSame(
            2,
            Integration::query()
                ->whereIn('provider', ['whatsapp', 'telegram'])
                ->count(),
        );
        $this->assertSame(
            2,
            PostCard::query()
                ->whereNull('post_id')
                ->whereIn('title', ['Perfil', 'Atención'])
                ->count(),
        );
        $this->assertSame(
            3,
            Category::query()
                ->whereIn('slug', ['acompanantes', 'masajes', 'perfiles-vip'])
                ->count(),
        );
        $this->assertSame(
            30,
            Post::query()
                ->where(fn ($query) => $query
                    ->where('slug', 'like', 'acompanantes-demo-%')
                    ->orWhere('slug', 'like', 'masajes-demo-%')
                    ->orWhere('slug', 'like', 'perfiles-vip-demo-%'))
                ->count(),
        );
        $this->assertSame(
            60,
            PostCard::query()
                ->whereNotNull('post_id')
                ->whereIn('title', ['Perfil', 'Atención'])
                ->count(),
        );

        $post = Post::query()->where('slug', 'acompanantes-demo-01')->firstOrFail();

        $this->assertCount(4, $post->gallery_image_urls);
        $this->assertNotEmpty($post->cover_image_url);
        $this->assertStringStartsWith('https://wa.me/51', $post->whatsapp_url);
        $this->assertStringStartsWith('https://t.me/', $post->telegram_url);
        $this->assertSame(2, $post->cards()->count());
    }
}
