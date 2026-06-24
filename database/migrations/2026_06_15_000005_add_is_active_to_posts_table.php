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
        if (Schema::hasColumn('posts', 'is_active')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('posts', 'is_active')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
