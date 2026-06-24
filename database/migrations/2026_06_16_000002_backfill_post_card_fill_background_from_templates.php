<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('post_cards', 'fill_background')) {
            return;
        }

        DB::table('post_cards')
            ->whereNull('post_id')
            ->where('fill_background', true)
            ->orderBy('id')
            ->get(['title', 'color', 'fill_background'])
            ->each(function (object $template): void {
                DB::table('post_cards')
                    ->whereNotNull('post_id')
                    ->where('title', $template->title)
                    ->where('color', $template->color)
                    ->update([
                        'fill_background' => $template->fill_background,
                    ]);
            });
    }

    public function down(): void
    {
        // Presentation backfill is intentionally not reversible.
    }
};
