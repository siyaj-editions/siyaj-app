<?php

namespace App\Service;

class StripeWebhookService
{
    public function __construct(private readonly StripeService $stripeService)
    {
    }

    public function handleWebhookPayload(string $payload, ?string $signature): void
    {
        if (!$signature) {
            throw new \InvalidArgumentException('No signature');
        }

        $this->stripeService->handleWebhook($payload, $signature);
    }
}
