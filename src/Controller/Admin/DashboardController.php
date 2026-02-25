<?php

namespace App\Controller\Admin;

use App\Repository\BookRepository;
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
        UserRepository $userRepository
    ): Response {
        $totalOrders = count($orderRepository->findAll());
        $totalBooks = count($bookRepository->findAll());
        $totalUsers = count($userRepository->findAll());

        $recentOrders = $orderRepository->findBy([], ['createdAt' => 'DESC'], 10);

        return $this->render('admin/dashboard/index.html.twig', [
            'totalOrders' => $totalOrders,
            'totalBooks' => $totalBooks,
            'totalUsers' => $totalUsers,
            'recentOrders' => $recentOrders,
        ]);
    }
}
