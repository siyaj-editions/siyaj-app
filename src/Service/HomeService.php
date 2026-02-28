<?php

namespace App\Service;

use App\Entity\Newsletter;
use App\Repository\BookRepository;
use App\Repository\NewsletterRepository;
use Doctrine\ORM\EntityManagerInterface;

class HomeService
{
    public function __construct(
        private readonly BookRepository $bookRepository,
        private readonly NewsletterRepository $newsletterRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function getLatestActiveBooks(int $limit = 6): array
    {
        return $this->bookRepository->findBy(
            ['isActive' => true],
            ['publishedAt' => 'DESC'],
            $limit
        );
    }

    public function subscribeToNewsletter(Newsletter $newsletter): NewsletterSubscriptionResult
    {
        $email = mb_strtolower(trim((string) $newsletter->getEmail()));
        $existing = $this->newsletterRepository->findOneBy(['email' => $email]);

        if ($existing) {
            return new NewsletterSubscriptionResult(
                'warning',
                'Cette adresse est déjà inscrite à la newsletter.'
            );
        }

        $newsletter->setEmail($email);
        $this->entityManager->persist($newsletter);
        $this->entityManager->flush();

        return new NewsletterSubscriptionResult(
            'success',
            'Inscription à la newsletter confirmée.'
        );
    }
}
