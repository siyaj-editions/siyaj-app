<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELED = 'canceled';
    case REFUNDED = 'refunded';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::PAID => 'Payée',
            self::CANCELED => 'Annulée',
            self::REFUNDED => 'Remboursée',
        };
    }

    public function getBadgeClass(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::CANCELED => 'secondary',
            self::REFUNDED => 'info',
        };
    }
}
