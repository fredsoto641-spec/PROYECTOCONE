<?php

namespace Tests\Feature;

use App\Models\Integration;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAdvertisePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_advertise_page_explains_types_benefits_and_example(): void
    {
        $response = $this->get(route('advertise'));

        $response
            ->assertOk()
            ->assertSee('Tipos de anuncio')
            ->assertSee('Anuncio estándar')
            ->assertSee('Anuncio destacado')
            ->assertSee('Beneficios')
            ->assertSee('Tu mejor fotografía aquí');
    }

    public function test_advertise_page_only_shows_active_whatsapp_and_telegram_integrations(): void
    {
        SiteSetting::current()->update([
            'contact_country' => 'Perú',
            'contact_phone' => '999999999',
            'contact_telegram_username' => 'comercial',
        ]);

        Integration::query()->create([
            'name' => 'WhatsApp comercial',
            'provider' => 'whatsapp',
            'base_url' => 'https://wa.me',
            'button_color' => '#25D366',
            'icon' => Integration::DEFAULT_ICONS['whatsapp'],
            'is_active' => true,
        ]);

        Integration::query()->create([
            'name' => 'Telegram oculto',
            'provider' => 'telegram',
            'base_url' => 'https://t.me/oculto',
            'button_color' => '#229ED9',
            'icon' => Integration::DEFAULT_ICONS['telegram'],
            'is_active' => false,
        ]);

        $response = $this->get(route('advertise'));

        $response
            ->assertOk()
            ->assertSee('Contactar por WhatsApp')
            ->assertSee('https://wa.me/51999999999')
            ->assertDontSee('Contactar por Telegram')
            ->assertDontSee('https://t.me/oculto');
    }

    public function test_advertise_page_builds_both_active_contact_links_from_general_settings(): void
    {
        SiteSetting::current()->update([
            'contact_country' => 'Colombia',
            'contact_phone' => '300 123 4567',
            'contact_telegram_username' => '@ventas_oficial',
        ]);

        Integration::query()->create([
            'name' => 'WhatsApp',
            'provider' => 'whatsapp',
            'base_url' => 'https://wa.me',
            'button_color' => '#25D366',
            'is_active' => true,
        ]);
        Integration::query()->create([
            'name' => 'Telegram',
            'provider' => 'telegram',
            'base_url' => 'https://t.me',
            'button_color' => '#229ED9',
            'is_active' => true,
        ]);

        $this->get(route('advertise'))
            ->assertOk()
            ->assertSee('https://wa.me/573001234567')
            ->assertSee('https://t.me/ventas_oficial')
            ->assertSee('Contactar por WhatsApp')
            ->assertSee('Contactar por Telegram');
    }
}
