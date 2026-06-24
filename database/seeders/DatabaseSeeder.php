<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            FooterSeeder::class,
            DemoContentSeeder::class,
        ]);

        // Resetear cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Posts
            'posts.view',
            'posts.create',
            'posts.edit',
            'posts.delete',
            'posts.publish',
            
            // Categorías
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'categories.publish',
            
            // Cards
            'cards.view',
            'cards.create',
            'cards.edit',
            'cards.delete',
            'cards.publish',
            
            // Integraciones
            'integrations.view',
            'integrations.create',
            'integrations.edit',
            'integrations.delete',
            'integrations.publish',
            
            // Configuración del sitio
            'site-settings.view',
            'site-settings.edit',
            
            // Usuarios
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Roles y permisos
            'roles.view',
            'roles.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        
        // Rol Admin: tiene todos los permisos
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());
        
        // Rol Editor: puede gestionar contenido pero no configuración
        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->syncPermissions([
            'posts.view',
            'posts.create',
            'posts.edit',
            'posts.delete',
            'posts.publish',
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'categories.publish',
            'cards.view',
            'cards.create',
            'cards.edit',
            'cards.delete',
            'cards.publish',
            'integrations.view',
        ]);
        
        // Rol Viewer: solo puede ver contenido
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
        $viewerRole->syncPermissions([
            'posts.view',
            'categories.view',
            'cards.view',
            'integrations.view',
        ]);

        // Crear usuario administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Administrador',
                'password' => 'Vidarte;123',
            ]
        );

        // Asignar rol admin
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        $this->command->info('✓ Roles creados: admin, editor, viewer');
        $this->command->info('✓ ' . count($permissions) . ' permisos creados y vinculados');
        $this->command->info('✓ Usuario administrador creado exitosamente.');
        $this->command->info('  Email: admin@test.com');
        $this->command->info('  Contraseña: Vidarte;123');
    }
}
