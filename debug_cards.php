<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cards = \App\Models\PostCard::all();

echo "Total cards: " . $cards->count() . "\n\n";

foreach ($cards as $card) {
    echo "ID: {$card->id}\n";
    echo "Title: {$card->title}\n";
    echo "post_id: " . ($card->post_id ?? 'NULL') . "\n";
    echo "is_active: " . ($card->is_active ? 'true' : 'false') . "\n";
    echo "fields: " . json_encode($card->fields) . "\n";
    echo "---\n\n";
}

$templateCards = \App\Models\PostCard::whereNull('post_id')->get();
echo "Template cards (post_id is NULL): " . $templateCards->count() . "\n";
