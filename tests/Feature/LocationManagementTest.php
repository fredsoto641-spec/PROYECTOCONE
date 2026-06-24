<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_locations_and_renaming_updates_posts(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('settings.locations.store'), [
                'name' => 'Distrito de prueba',
                'department' => 'Lima',
                'sort_order' => 10,
            ])
            ->assertSessionHasNoErrors();

        $location = Location::query()->where('name', 'Distrito de prueba')->firstOrFail();
        $category = Category::query()->create([
            'name' => 'Categoría',
            'slug' => 'categoria',
            'is_active' => true,
        ]);
        $post = Post::query()->create([
            'category_id' => $category->id,
            'title' => 'Post',
            'slug' => 'post',
            'body' => 'Contenido',
            'location' => $location->name,
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->put(route('settings.locations.update', $location), [
                'name' => 'Distrito actualizado',
                'department' => 'Callao',
                'sort_order' => 20,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Distrito actualizado', $post->refresh()->location);

        $this->actingAs($admin)
            ->delete(route('settings.locations.destroy', $location->refresh()))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }

    public function test_post_location_is_required_and_must_exist_in_catalog(): void
    {
        $admin = $this->admin();
        $category = Category::query()->create([
            'name' => 'Categoría',
            'slug' => 'categoria',
            'is_active' => true,
        ]);

        $payload = [
            'category_id' => $category->id,
            'title' => 'Post sin ubicación',
            'body' => 'Contenido',
            'publish_mode' => 'immediate',
        ];

        $this->actingAs($admin)
            ->post(route('posts.store'), $payload)
            ->assertSessionHasErrors('location');

        $this->actingAs($admin)
            ->post(route('posts.store'), $payload + ['location' => 'Ubicación inexistente'])
            ->assertSessionHasErrors('location');
    }

    public function test_location_list_is_paginated_and_duplicate_names_are_rejected(): void
    {
        $admin = $this->admin();

        foreach (range(1, 20) as $number) {
            Location::query()->create([
                'name' => "Distrito {$number}",
                'department' => 'Lima',
                'sort_order' => $number,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertViewHas('locations', fn ($locations): bool => $locations->count() === 15 && $locations->hasPages());

        $this->actingAs($admin)
            ->post(route('settings.locations.store'), [
                'name' => '  distrito 1 ',
                'department' => 'Lima',
                'sort_order' => 0,
            ])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Location::query()->where('name', 'Distrito 1')->count());
    }

    private function admin(): User
    {
        $role = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
