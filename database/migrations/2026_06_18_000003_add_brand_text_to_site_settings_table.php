<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('brand_primary_text', 80)->default('Conejitas')->after('id');
            $table->string('brand_accent_text', 80)->default('Hot')->after('brand_primary_text');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['brand_primary_text', 'brand_accent_text']);
        });
    }
};
