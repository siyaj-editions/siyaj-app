<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Service\AdminOrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/commandes')]
#[IsGranted('ROLE_ADMIN')]
class OrderController extends AbstractController
{
    #[Route('', name: 'app_admin_order_index')]
    public function index(AdminOrderService $adminOrderService): Response
    {
        return $this->render('admin/order/index.html.twig', [
            'orders' => $adminOrderService->listOrders(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_order_show')]
    public function show(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/status', name: 'app_admin_order_status', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        Order $order,
        AdminOrderService $adminOrderService
    ): Response {
        $status = $request->request->get('status');
        if ($adminOrderService->updateOrderStatus($order, is_string($status) ? $status : null)) {
            $this->addFlash('success', 'Le statut de la commande a été mis à jour.');
        }

        return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
    }

    #[Route('/{id}/tracking', name: 'app_admin_order_tracking', methods: ['POST'])]
    public function updateTracking(
        Request $request,
        Order $order,
        AdminOrderService $adminOrderService
    ): Response {
        if (!$this->isCsrfTokenValid('update_tracking_' . $order->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
        }

        $result = $adminOrderService->updateTrackingNumber($order, $request->request->get('tracking_number'));

        if ($result === 'updated') {
            $this->addFlash('success', 'Le numéro de suivi a été enregistré et la commande a été marquée comme envoyée.');
        } elseif ($result === 'email_failed') {
            $this->addFlash('warning', 'Le numéro de suivi a été enregistré, mais l’email client n’a pas pu être envoyé.');
        } else {
            $this->addFlash('error', 'Veuillez renseigner un numéro de suivi valide.');
        }

        return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
    }
}
