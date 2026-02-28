<?php

namespace App\Service;

use App\Entity\Order;

class CheckoutSuccessResult
{
    public function __construct(
        public readonly ?Order $order,
        public readonly bool $stripeSessionFound
    ) {
    }
}
