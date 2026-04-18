<?php

namespace App\Service;

use App\Entity\ManuscriptSubmission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AuthorSubmissionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ManuscriptStorage $manuscriptStorage,
    )
    {
    }

    public function submit(ManuscriptSubmission $submission, ?UploadedFile $manuscriptFile = null): void
    {
        $submission->setEmail(mb_strtolower(trim((string) $submission->getEmail())));

        if ($manuscriptFile instanceof UploadedFile) {
            $submission->setManuscriptPath($this->manuscriptStorage->upload($manuscriptFile));
        }

        $this->entityManager->persist($submission);
        $this->entityManager->flush();
    }
}
