<?php

namespace App\Service;

class CartActionResult
{
    /**
     * @param array<string, string|int> $routeParameters
     */
    public function __construct(
        public readonly ?string $flashType,
        public readonly ?string $flashMessage,
        public readonly string $redirectRoute,
        public readonly array $routeParameters = []
    ) {
    }
}
