<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('integrations', 'button_color')) {
            return;
        }

        Schema::table('integrations', function (Blueprint $table) {
            $table->string('button_color', 16)->default('#222222')->after('base_url');
            $table->string('icon')->default('heroicon-o-link')->after('button_color');
        });

        foreach ([
            'whatsapp' => ['button_color' => '#25D366', 'icon' => 'heroicon-o-chat-bubble-left-right'],
            'telegram' => ['button_color' => '#229ED9', 'icon' => 'heroicon-o-paper-airplane'],
            'sms' => ['button_color' => '#222222', 'icon' => 'heroicon-o-device-phone-mobile'],
        ] as $provider => $presentation) {
            DB::table('integrations')
                ->where('provider', $provider)
                ->update($presentation);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('integrations', 'button_color')) {
            return;
        }

        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn(['button_color', 'icon']);
        });
    }
};
