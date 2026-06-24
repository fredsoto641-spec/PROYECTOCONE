<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('whatsapp_country_code', 8)->nullable()->after('gallery_image_urls');
            $table->string('whatsapp_number', 32)->nullable()->after('whatsapp_country_code');
            $table->string('telegram_username', 64)->nullable()->after('whatsapp_url');
            $table->string('sms_country_code', 8)->nullable()->after('telegram_url');
            $table->string('sms_number', 32)->nullable()->after('sms_country_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_country_code',
                'whatsapp_number',
                'telegram_username',
                'sms_country_code',
                'sms_number',
            ]);
        });
    }
};
