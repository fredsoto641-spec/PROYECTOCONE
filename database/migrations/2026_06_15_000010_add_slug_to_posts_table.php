<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('posts', 'slug')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        $usedSlugs = [];

        DB::table('posts')
            ->select(['id', 'title'])
            ->orderBy('id')
            ->get()
            ->each(function (object $post) use (&$usedSlugs): void {
                $slug = $this->uniqueSlug((string) $post->title, $usedSlugs);
                $usedSlugs[] = $slug;

                DB::table('posts')
                    ->where('id', $post->id)
                    ->update(['slug' => $slug]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('posts', 'slug')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }

    /**
     * @param array<int, string> $usedSlugs
     */
    private function uniqueSlug(string $title, array $usedSlugs): string
    {
        $base = Str::slug($title) ?: 'post';

        do {
            $slug = $base.'-'.random_int(1000, 9999);
        } while (in_array($slug, $usedSlugs, true) || DB::table('posts')->where('slug', $slug)->exists());

        return $slug;
    }
};
