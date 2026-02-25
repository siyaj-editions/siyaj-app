<?php

namespace App\Enum;

enum BookFormat: string
{
    case PHYSICAL = 'physical';
    case DIGITAL = 'digital';

    public function getLabel(): string
    {
        return match($this) {
            self::PHYSICAL => 'Physique',
            self::DIGITAL => 'Numérique',
        };
    }
}
