<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    public function __construct(
        private string $stripeSecretKey,
        private string $stripeWebhookSecret,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Crée une session Stripe Checkout
     */
    public function createCheckoutSession(Order $order): Session
    {
        $lineItems = [];

        foreach ($order->getOrderItems() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($order->getCurrency()),
                    'product_data' => [
                        'name' => $item->getTitleSnapshot(),
                    ],
                    'unit_amount' => $item->getPriceSnapshot(),
                ],
                'quantity' => $item->getQuantity(),
            ];
        }

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->urlGenerator->generate('app_checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->urlGenerator->generate('app_checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'client_reference_id' => (string) $order->getId(),
                'metadata' => [
                    'order_id' => $order->getId(),
                ],
            ]);

            $order->setStripeSessionId($session->id);
            $this->entityManager->flush();

            return $session;
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Erreur lors de la création de la session Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Traite le webhook Stripe
     */
    public function handleWebhook(string $payload, string $signature): void
    {
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $this->stripeWebhookSecret
            );
        } catch (\Exception $e) {
            throw new \RuntimeException('Webhook signature verification failed: ' . $e->getMessage());
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $this->handleCheckoutSessionCompleted($session);
        }
    }

    /**
     * Gère la confirmation du paiement
     */
    private function handleCheckoutSessionCompleted($session): void
    {
        $orderId = $session->metadata->order_id ?? null;

        if (!$orderId) {
            return;
        }

        $order = $this->entityManager->getRepository(Order::class)->find($orderId);

        if (!$order) {
            return;
        }

        // Marquer la commande comme payée
        $order->setStatus(OrderStatus::PAID);

        // Décrémenter le stock pour chaque article
        foreach ($order->getOrderItems() as $orderItem) {
            $book = $orderItem->getBook();
            $book->decrementStock($orderItem->getQuantity());
        }

        $this->entityManager->flush();
    }

    /**
     * Récupère une session Stripe
     */
    public function retrieveSession(string $sessionId): ?Session
    {
        try {
            return Session::retrieve($sessionId);
        } catch (ApiErrorException $e) {
            return null;
        }
    }
}
