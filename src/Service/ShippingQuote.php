<?php

namespace App\Service;

final readonly class ShippingQuote
{
    public function __construct(
        public string $zoneCode,
        public string $zoneLabel,
        public string $methodCode,
        public string $methodLabel,
        public int $costCents,
        public string $delayLabel,
        public int $itemsSubtotalCents,
        public int $totalCents,
        public bool $isFreeShipping
    ) {
    }

    public function getCostEuros(): float
    {
        return $this->costCents / 100;
    }

    public function getTotalEuros(): float
    {
        return $this->totalCents / 100;
    }
}
