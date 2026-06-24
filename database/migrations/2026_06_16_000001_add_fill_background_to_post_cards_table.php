<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('post_cards', 'fill_background')) {
            return;
        }

        Schema::table('post_cards', function (Blueprint $table) {
            $table->boolean('fill_background')->default(false)->after('color');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('post_cards', 'fill_background')) {
            return;
        }

        Schema::table('post_cards', function (Blueprint $table) {
            $table->dropColumn('fill_background');
        });
    }
};
