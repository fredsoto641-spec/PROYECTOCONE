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
        if (Schema::hasColumn('post_cards', 'color')) {
            return;
        }

        Schema::table('post_cards', function (Blueprint $table) {
            $table->string('color', 16)->default('#E91E63')->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('post_cards', 'color')) {
            return;
        }

        Schema::table('post_cards', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
