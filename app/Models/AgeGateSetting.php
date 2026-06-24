<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

#[Fillable([
    'is_enabled',
    'storage_key',
    'badge',
    'title',
    'description',
    'confirm_label',
    'exit_label',
    'exit_href',
    'legal_text',
])]
class AgeGateSetting extends Model
{
    public const DEFAULTS = [
        'is_enabled' => true,
        'storage_key' => 'gatitas_hot_age_confirmed',
        'badge' => '+18',
        'title' => 'Contenido sensible para adultos',
        'description' => 'Este sitio contiene anuncios y material dirigido exclusivamente a personas mayores de edad. Para continuar, confirma que tienes 18 años o más y que aceptas acceder bajo tu responsabilidad.',
        'confirm_label' => 'Soy mayor de edad',
        'exit_label' => 'Salir',
        'exit_href' => 'https://www.google.com',
        'legal_text' => 'Al continuar declaras que cumples con la edad legal requerida en tu jurisdicción.',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        if (! Schema::hasTable('age_gate_settings')) {
            return new self(self::DEFAULTS);
        }

        return self::query()->firstOrCreate(['id' => 1], self::DEFAULTS);
    }

    /**
     * @return array<string, bool|string>
     */
    public function toModalContent(): array
    {
        return [
            'enabled' => $this->is_enabled,
            'storageKey' => $this->storage_key,
            'badge' => $this->badge,
            'title' => $this->title,
            'description' => $this->description,
            'confirmLabel' => $this->confirm_label,
            'exitLabel' => $this->exit_label,
            'exitHref' => $this->exit_href,
            'legalText' => $this->legal_text,
        ];
    }
}
