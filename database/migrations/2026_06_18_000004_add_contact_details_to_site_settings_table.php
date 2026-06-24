<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('contact_country')->default('Perú')->after('brand_accent_text');
            $table->string('contact_phone', 32)->nullable()->after('contact_country');
            $table->string('contact_telegram_username', 64)->nullable()->after('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'contact_country',
                'contact_phone',
                'contact_telegram_username',
            ]);
        });
    }
};
