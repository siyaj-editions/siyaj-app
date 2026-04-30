<?php

namespace App\Enum;

enum OrderSend: string
{
    case PROCESSING = 'processing';
    case SENT = 'sent';
    case RECEIVED = 'received';

    public function getLabel(): string
    {
        return match ($this) {
            self::PROCESSING => 'En cours de traitement',
            self::SENT => 'Envoyée',
            self::RECEIVED => 'Reçue',
        };
    }
}
