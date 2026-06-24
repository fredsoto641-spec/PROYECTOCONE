<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('department');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('locations')->insert([
            'name' => 'Lima',
            'department' => 'Lima',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('posts')
            ->whereNull('location')
            ->orWhere('location', '')
            ->update(['location' => 'Lima']);

        DB::table('posts')
            ->select('location')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->each(function (string $location): void {
                DB::table('locations')->updateOrInsert(
                    ['name' => trim($location)],
                    [
                        'department' => 'Perú',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            });

        Schema::table('posts', function (Blueprint $table) {
            $table->string('location')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('location')->nullable()->change();
        });

        Schema::dropIfExists('locations');
    }
};
