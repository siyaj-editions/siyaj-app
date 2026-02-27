<?php

namespace App\Service;

use App\Repository\BookRepository;
use App\Repository\ManuscriptSubmissionRepository;
use App\Repository\NewsletterRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;

class AdminDashboardService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly BookRepository $bookRepository,
        private readonly UserRepository $userRepository,
        private readonly NewsletterRepository $newsletterRepository,
        private readonly ManuscriptSubmissionRepository $manuscriptSubmissionRepository
    ) {
    }

    public function buildDashboardData(): array
    {
        return [
            'totalOrders' => $this->orderRepository->count([]),
            'totalBooks' => $this->bookRepository->count([]),
            'totalUsers' => $this->userRepository->count([]),
            'totalNewsletter' => $this->newsletterRepository->count([]),
            'totalManuscripts' => $this->manuscriptSubmissionRepository->count([]),
            'recentOrders' => $this->orderRepository->findLatestForToday(10),
        ];
    }
}
