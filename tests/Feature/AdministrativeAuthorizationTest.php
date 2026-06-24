<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdministrativeAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_list_posts_but_cannot_create_them(): void
    {
        $viewer = $this->userWithPermissions('viewer', ['posts.view']);

        $this->actingAs($viewer)
            ->get(route('posts.index'))
            ->assertOk();

        $this->actingAs($viewer)
            ->get(route('posts.create'))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('posts.store'), [])
            ->assertForbidden();
    }

    public function test_editor_can_create_and_edit_categories_but_cannot_publish_without_permission(): void
    {
        $editor = $this->userWithPermissions('editor', [
            'categories.view',
            'categories.create',
            'categories.edit',
        ]);
        $category = Category::query()->create([
            'name' => 'Categoría',
            'slug' => 'categoria',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($editor)
            ->get(route('categories.create'))
            ->assertOk();

        $this->actingAs($editor)
            ->get(route('categories.edit', $category))
            ->assertOk();

        $this->actingAs($editor)
            ->patch(route('categories.toggle-visibility', $category))
            ->assertForbidden();
    }

    public function test_publish_permission_allows_visibility_changes(): void
    {
        $publisher = $this->userWithPermissions('publisher', [
            'categories.view',
            'categories.publish',
        ]);
        $category = Category::query()->create([
            'name' => 'Categoría',
            'slug' => 'categoria',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($publisher)
            ->patch(route('categories.toggle-visibility', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertFalse($category->refresh()->is_active);
    }

    public function test_admin_role_keeps_full_access_as_a_superuser(): void
    {
        $admin = $this->userWithPermissions('admin', []);

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('posts.create'))
            ->assertOk();
    }

    /**
     * @param array<int, string> $permissions
     */
    private function userWithPermissions(string $roleName, array $permissions): User
    {
        $role = Role::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $role->syncPermissions($permissions);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
