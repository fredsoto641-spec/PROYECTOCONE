<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class FooterSeeder extends Seeder
{
    public function run(): void
    {
        $settings = SiteSetting::query()->firstOrCreate(
            ['id' => 1],
            SiteSetting::DEFAULTS,
        );

        if (blank($settings->footer_columns)) {
            $settings->update([
                'footer_columns' => SiteSetting::DEFAULT_FOOTER_COLUMNS,
            ]);
        }
    }
}
