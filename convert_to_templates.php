<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Convirtiendo cards del post #8 a plantillas...\n\n";

$cards = \App\Models\PostCard::where('post_id', 8)->get();

if ($cards->isEmpty()) {
    echo "No se encontraron cards asociadas al post #8.\n";
    exit;
}

echo "Se encontraron {$cards->count()} cards:\n";
foreach ($cards as $card) {
    echo "- {$card->title}\n";
}

echo "\nConvirtiendo a plantillas...\n\n";

foreach ($cards as $card) {
    $card->post_id = null;
    $card->save();
    echo "✓ {$card->title} convertida a plantilla\n";
}

echo "\n✅ ¡Conversión completada! Las cards ahora aparecerán en el listado de plantillas.\n";
