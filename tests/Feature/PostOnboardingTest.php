<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PostOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_post_uses_the_six_step_onboarding(): void
    {
        $this->actingAs($this->admin())
            ->get(route('posts.create'))
            ->assertOk()
            ->assertSee('Datos básicos')
            ->assertSee('Contenido')
            ->assertSee('Imágenes')
            ->assertSee('Contacto')
            ->assertSee('Cards')
            ->assertSee('Publicación')
            ->assertSee('Paso 1 de 6');
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
