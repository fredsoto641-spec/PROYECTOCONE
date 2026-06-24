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
        if (Schema::hasColumn('posts', 'published_at')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('posts', 'published_at')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
