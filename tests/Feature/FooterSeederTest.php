<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Database\Seeders\FooterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_seeder_initializes_the_default_columns(): void
    {
        $this->seed(FooterSeeder::class);

        $this->assertSame(
            SiteSetting::DEFAULT_FOOTER_COLUMNS,
            SiteSetting::query()->findOrFail(1)->footer_columns,
        );
    }

    public function test_footer_seeder_preserves_existing_custom_columns(): void
    {
        $customColumns = [
            [
                'title' => 'Personalizado',
                'items' => [
                    ['label' => 'Mi enlace', 'href' => '/mi-enlace'],
                ],
            ],
        ];

        SiteSetting::query()->create([
            ...SiteSetting::DEFAULTS,
            'id' => 1,
            'footer_columns' => $customColumns,
        ]);

        $this->seed(FooterSeeder::class);

        $this->assertSame(
            $customColumns,
            SiteSetting::query()->findOrFail(1)->footer_columns,
        );
    }
}
