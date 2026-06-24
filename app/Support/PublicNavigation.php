<?php

namespace App\Support;

class PublicNavigation
{
    /**
     * @return array<int, array{label: string, href: string}>
     */
    public static function links(?string $baseUrl = null): array
    {
        $baseUrl = $baseUrl ? rtrim($baseUrl, '/') : '';

        return [
            ['label' => 'Inicio', 'href' => $baseUrl.'#inicio'],
            ['label' => 'Categorías', 'href' => $baseUrl.'#categorias'],
            ['label' => 'Destacados', 'href' => $baseUrl.'#destacados'],
            ['label' => 'Recientes', 'href' => $baseUrl.'#recientes'],
            ['label' => 'Ubicaciones', 'href' => url('/u')],
            ['label' => 'Etiquetas', 'href' => url('/t')],
        ];
    }
}
