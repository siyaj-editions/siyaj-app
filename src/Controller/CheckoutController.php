<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Service\CartService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;

#[Route('/checkout')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private StripeService $stripeService,
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository
    ) {}

    #[Route('/create', name: 'app_checkout_create', methods: ['POST'])]
    public function create(): Response
    {
        if ($this->cartService->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        // Valider le stock
        $stockErrors = $this->cartService->validateStock();
        if (!empty($stockErrors)) {
            foreach ($stockErrors as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('app_cart');
        }

        // Créer la commande
        /** @var User $user */
        $user = $this->getUser();
        $order = new Order();
        $order->setUser($user);
        $order->setCurrency('EUR');

        $cart = $this->cartService->getFullCart();

        foreach ($cart as $item) {
            $orderItem = new OrderItem();
            $orderItem->setBook($item['book']);
            $orderItem->setTitleSnapshot($item['book']->getTitle());
            $orderItem->setPriceSnapshot($item['book']->getPriceCents());
            $orderItem->setQuantity($item['quantity']);
            $order->addOrderItem($orderItem);
        }

        $order->calculateTotal();

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        try {
            $session = $this->stripeService->createCheckoutSession($order);

            if (!$session->url) {
                throw new \RuntimeException('URL Stripe manquante');
            }

            return $this->redirect($session->url);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création du paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/start', name: 'app_checkout_start_post', methods: ['POST'])]
    public function startPost(): Response
    {
        if ($this->cartService->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        $stockErrors = $this->cartService->validateStock();
        if (!empty($stockErrors)) {
            foreach ($stockErrors as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('app_cart');
        }

        /** @var User $user */
        $user = $this->getUser();
        $order = new Order();
        $order->setUser($user);
        $order->setCurrency('EUR');

        $cart = $this->cartService->getFullCart();

        foreach ($cart as $item) {
            $orderItem = new OrderItem();
            $orderItem->setBook($item['book']);
            $orderItem->setTitleSnapshot($item['book']->getTitle());
            $orderItem->setPriceSnapshot($item['book']->getPriceCents());
            $orderItem->setQuantity($item['quantity']);
            $order->addOrderItem($orderItem);
        }

        $order->calculateTotal();

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        try {
            $session = $this->stripeService->createCheckoutSession($order);

            return $this->redirectToRoute('app_checkout_start', [
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création du paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/start', name: 'app_checkout_start', methods: ['GET'])]
    public function start(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');
        if (!$sessionId) {
            return $this->redirectToRoute('app_cart');
        }

        $session = $this->stripeService->retrieveSession($sessionId);

        return $this->render('checkout/start.html.twig', [
            'stripePublicKey' => $this->getParameter('stripe_public_key'),
            'sessionId' => $sessionId,
            'sessionUrl' => $session?->url,
        ]);
    }

    #[Route('/success', name: 'app_checkout_success')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('app_home');
        }

        // Debug: ensure Stripe can actually retrieve the session
        $stripeSession = $this->stripeService->retrieveSession($sessionId);
        if (!$stripeSession) {
            $this->addFlash('error', 'Session Stripe introuvable (clé incorrecte ou session expirée).');
        } else {
            // Fallback: si le webhook n'est pas arrivé, on synchronise ici.
            $this->stripeService->syncPaidOrderFromSession($stripeSession);
        }

        $order = $this->orderRepository->findByStripeSessionId($sessionId);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        // Vider le panier seulement après confirmation du paiement
        if ($order->isPaid()) {
            $this->cartService->clear();
        }

        return $this->render('checkout/success.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/cancel', name: 'app_checkout_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé.');

        return $this->render('checkout/cancel.html.twig');
    }

    #[Route('/debug', name: 'app_checkout_debug', methods: ['GET'])]
    public function debug(Request $request): JsonResponse
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return new JsonResponse(['error' => 'session_id manquant'], 400);
        }

        $session = $this->stripeService->retrieveSession($sessionId);
        if (!$session) {
            return new JsonResponse(['error' => 'Session Stripe introuvable'], 404);
        }

        return new JsonResponse([
            'id' => $session->id ?? null,
            'status' => $session->status ?? null,
            'payment_status' => $session->payment_status ?? null,
            'livemode' => $session->livemode ?? null,
            'expires_at' => $session->expires_at ?? null,
            'url' => $session->url ?? null,
        ]);
    }
}
