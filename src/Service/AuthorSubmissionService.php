<?php

namespace App\Service;

use App\Entity\ManuscriptSubmission;
use Doctrine\ORM\EntityManagerInterface;

class AuthorSubmissionService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function submit(ManuscriptSubmission $submission): void
    {
        $submission->setEmail(mb_strtolower(trim((string) $submission->getEmail())));

        $this->entityManager->persist($submission);
        $this->entityManager->flush();
    }
}
