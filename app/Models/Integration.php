<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'provider', 'base_url', 'button_color', 'icon', 'credentials', 'is_active'])]
class Integration extends Model
{
    use HasFactory;

    public const PROVIDERS = [
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'sms' => 'SMS',
        'custom' => 'Personalizado',
    ];

    public const DEFAULT_ICONS = [
        'whatsapp' => 'heroicon-o-chat-bubble-left-right',
        'telegram' => 'heroicon-o-paper-airplane',
        'sms' => 'heroicon-o-device-phone-mobile',
        'custom' => 'heroicon-o-link',
    ];

    public const ICON_OPTIONS = [
        'heroicon-o-chat-bubble-left-right' => 'Chat',
        'heroicon-o-paper-airplane' => 'Avión de papel',
        'heroicon-o-device-phone-mobile' => 'Teléfono',
        'heroicon-o-link' => 'Enlace',
        'heroicon-o-globe-alt' => 'Globo',
        'heroicon-o-envelope' => 'Correo',
        'heroicon-o-bolt' => 'Rayo',
        'heroicon-o-star' => 'Estrella',
        'heroicon-o-heart' => 'Corazón',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credentials' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
