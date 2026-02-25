<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Service\CartService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private StripeService $stripeService,
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository
    ) {
    }

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
        $order = new Order();
        $order->setUser($this->getUser());
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

            return $this->redirect($session->url);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création du paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/success', name: 'app_checkout_success')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('app_home');
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

    #[Route('/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature');

        if (!$signature) {
            return new Response('No signature', 400);
        }

        try {
            $this->stripeService->handleWebhook($payload, $signature);

            return new Response('Webhook handled', 200);
        } catch (\Exception $e) {
            return new Response('Webhook error: ' . $e->getMessage(), 400);
        }
    }
}
