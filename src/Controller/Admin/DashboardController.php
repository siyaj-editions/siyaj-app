<?php

namespace App\Controller\Admin;

use App\Repository\BookRepository;
use App\Repository\ManuscriptSubmissionRepository;
use App\Repository\NewsletterRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function index(
        OrderRepository $orderRepository,
        BookRepository $bookRepository,
        UserRepository $userRepository,
        NewsletterRepository $newsletterRepository,
        ManuscriptSubmissionRepository $manuscriptSubmissionRepository
    ): Response {
        $totalOrders = count($orderRepository->findAll());
        $totalBooks = count($bookRepository->findAll());
        $totalUsers = count($userRepository->findAll());
        $totalNewsletter = count($newsletterRepository->findAll());
        $totalManuscripts = count($manuscriptSubmissionRepository->findAll());

        $recentOrders = $orderRepository->findLatestForToday(10);

        return $this->render('admin/dashboard/index.html.twig', [
            'totalOrders' => $totalOrders,
            'totalBooks' => $totalBooks,
            'totalUsers' => $totalUsers,
            'totalNewsletter' => $totalNewsletter,
            'totalManuscripts' => $totalManuscripts,
            'recentOrders' => $recentOrders,
        ]);
    }
}
