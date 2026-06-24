<?php

namespace Tests\Feature;

use App\Models\AgeGateSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FooterSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_footer_columns_and_the_public_footer_uses_them(): void
    {
        $columns = [
            [
                'title' => 'Recursos',
                'items' => [
                    ['label' => 'Guía personalizada', 'href' => '/#guia'],
                    ['label' => 'Publicar ahora', 'href' => '/publicar-anuncio'],
                ],
            ],
            [
                'title' => 'Legal',
                'items' => [
                    ['label' => 'Privacidad personalizada', 'href' => 'https://example.com/privacidad'],
                ],
            ],
        ];

        $this->actingAs($this->admin())
            ->put(route('settings.update'), $this->settingsPayload($columns))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('settings.edit').'#footer');

        $this->assertSame($columns, SiteSetting::current()->refresh()->footer_columns);

        $this->get('/')
            ->assertOk()
            ->assertSee('Recursos')
            ->assertSee('Guía personalizada')
            ->assertSee('/#guia')
            ->assertSee('Privacidad personalizada')
            ->assertDontSee('Centro de ayuda');
    }

    public function test_footer_rejects_unsafe_links(): void
    {
        $columns = [
            [
                'title' => 'Enlaces',
                'items' => [
                    ['label' => 'Enlace inseguro', 'href' => 'javascript:alert(1)'],
                ],
            ],
        ];

        $this->actingAs($this->admin())
            ->from(route('settings.edit').'#footer')
            ->put(route('settings.update'), $this->settingsPayload($columns))
            ->assertSessionHasErrors('footer_columns');
    }

    /**
     * @param  array<int, array{title: string, items: array<int, array{label: string, href: string}>}>  $columns
     * @return array<string, mixed>
     */
    private function settingsPayload(array $columns): array
    {
        $site = SiteSetting::DEFAULTS;
        $age = AgeGateSetting::DEFAULTS;

        return [
            'settings_section' => 'footer',
            'brand_primary_text' => $site['brand_primary_text'],
            'brand_accent_text' => $site['brand_accent_text'],
            'contact_country' => $site['contact_country'],
            'contact_phone' => $site['contact_phone'],
            'contact_telegram_username' => $site['contact_telegram_username'],
            'site_title' => $site['site_title'],
            'site_subtitle' => $site['site_subtitle'],
            'cover_image_url' => $site['cover_image_url'],
            'primary_color' => $site['primary_color'],
            'primary_hover_color' => $site['primary_hover_color'],
            'text_color' => $site['text_color'],
            'muted_color' => $site['muted_color'],
            'background_color' => $site['background_color'],
            'admin_ink_color' => $site['admin_ink_color'],
            'admin_ink_hover_color' => $site['admin_ink_hover_color'],
            'admin_muted_color' => $site['admin_muted_color'],
            'admin_danger_color' => $site['admin_danger_color'],
            'admin_focus_color' => $site['admin_focus_color'],
            'server_country' => $site['server_country'],
            'server_country_code' => $site['server_country_code'],
            'server_utc_offset' => $site['server_utc_offset'],
            'footer_columns' => json_encode($columns, JSON_THROW_ON_ERROR),
            'age_gate_is_enabled' => '1',
            'age_gate_storage_key' => $age['storage_key'],
            'age_gate_badge' => $age['badge'],
            'age_gate_title' => $age['title'],
            'age_gate_description' => $age['description'],
            'age_gate_confirm_label' => $age['confirm_label'],
            'age_gate_exit_label' => $age['exit_label'],
            'age_gate_exit_href' => $age['exit_href'],
            'age_gate_legal_text' => $age['legal_text'],
        ];
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
