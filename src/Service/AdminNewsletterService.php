<?php

namespace App\Service;

use App\Repository\NewsletterRepository;

class AdminNewsletterService
{
    public function __construct(private readonly NewsletterRepository $newsletterRepository)
    {
    }

    public function listLatestSubscriptions(int $limit = 200): array
    {
        return $this->newsletterRepository->findLatest($limit);
    }
}
