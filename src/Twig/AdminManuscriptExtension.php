<?php

namespace App\Twig;

use App\Repository\ManuscriptSubmissionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminManuscriptExtension extends AbstractExtension
{
    public function __construct(
        private readonly ManuscriptSubmissionRepository $manuscriptSubmissionRepository,
        private readonly Security $security
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_unread_manuscripts_count', [$this, 'getUnreadCount']),
        ];
    }

    public function getUnreadCount(): int
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return 0;
        }

        return $this->manuscriptSubmissionRepository->countUnread();
    }
}
