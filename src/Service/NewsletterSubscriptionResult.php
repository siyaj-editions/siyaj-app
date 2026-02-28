<?php

namespace App\Service;

class NewsletterSubscriptionResult
{
    public function __construct(
        public readonly string $flashType,
        public readonly string $flashMessage
    ) {
    }
}
