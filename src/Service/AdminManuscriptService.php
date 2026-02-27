<?php

namespace App\Service;

use App\Entity\ManuscriptSubmission;
use App\Repository\ManuscriptSubmissionRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminManuscriptService
{
    public function __construct(
        private readonly ManuscriptSubmissionRepository $manuscriptSubmissionRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return ManuscriptSubmission[]
     */
    public function listLatestSubmissions(int $limit = 300): array
    {
        return $this->manuscriptSubmissionRepository->findLatest($limit);
    }

    public function markAsRead(ManuscriptSubmission $manuscriptSubmission): void
    {
        $manuscriptSubmission->setIsReadByAdmin(true);
        $this->entityManager->flush();
    }

    public function markAsUnread(ManuscriptSubmission $manuscriptSubmission): void
    {
        $manuscriptSubmission->setIsReadByAdmin(false);
        $this->entityManager->flush();
    }
}
