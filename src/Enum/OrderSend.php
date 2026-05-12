<?php

namespace App\Enum;

enum OrderSend: string
{
    case PROCESSING = 'processing';
    case SENT = 'sent';
    case READY_FOR_PICKUP = 'ready_for_pickup';
    case RECEIVED = 'received';
    case CANCELED = 'canceled';

    public function getLabel(): string
    {
        return match ($this) {
            self::PROCESSING => 'En cours de traitement',
            self::SENT => 'Envoyée',
            self::READY_FOR_PICKUP => 'Prête à être retirée',
            self::RECEIVED => 'Reçue',
            self::CANCELED => 'Annulée',
        };
    }
}
