<?php

namespace App\Service;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class StripeService
{
    public function __construct(
        private string $stripeSecretKey,
        private string $stripeWebhookSecret,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
        $this->validateConfig();
        Stripe::setApiKey($this->stripeSecretKey);
    }

    private function validateConfig(): void
    {
        if (!str_starts_with($this->stripeSecretKey, 'sk_')) {
            throw new \RuntimeException('STRIPE_SECRET_KEY invalide (doit commencer par sk_)');
        }
        if ($this->stripeWebhookSecret === '') {
            throw new \RuntimeException('STRIPE_WEBHOOK_SECRET manquant');
        }
    }

    public function createCheckoutSession(Order $order): Session
    {
        $lineItems = [];

        foreach ($order->getOrderItems() as $item) {
            $unitAmount = (int) $item->getPriceSnapshot();     // centimes
            $quantity   = (int) $item->getQuantity();

            if ($unitAmount <= 0) {
                throw new \LogicException('Montant Stripe invalide: ' . $unitAmount);
            }
            if ($quantity <= 0) {
                throw new \LogicException('Quantité invalide: ' . $quantity);
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($order->getCurrency()),
                    'product_data' => [
                        'name' => (string) $item->getTitleSnapshot(),
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => $quantity,
            ];
        }

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->urlGenerator->generate(
                    'app_checkout_success',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->urlGenerator->generate(
                    'app_checkout_cancel',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'client_reference_id' => (string) $order->getId(),
                'metadata' => [
                    'order_id' => (string) $order->getId(),
                ],
            ]);

            $this->logger->info('Stripe checkout session created', [
                'order_id' => $order->getId(),
                'session_id' => $session->id ?? null,
                'session_url' => $session->url ?? null,
            ]);

            if (!$session->id || !$session->url) {
                throw new \RuntimeException('Session Stripe invalide (id/url manquants)');
            }

            $order->setStripeSessionId($session->id);
            $this->entityManager->flush();

            return $session;
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe session creation failed', [
                'order_id' => $order->getId(),
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Erreur lors de la création de la session Stripe: ' . $e->getMessage(), 0, $e);
        }
    }

    public function handleWebhook(string $payload, string $signature): void
    {
        try {
            $event = Webhook::constructEvent($payload, $signature, $this->stripeWebhookSecret);
        } catch (SignatureVerificationException $e) {
            $this->logger->warning('Stripe webhook signature verification failed', [
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Invalid webhook signature', 0, $e);
        } catch (\UnexpectedValueException $e) {
            $this->logger->warning('Stripe webhook payload invalid', [
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Invalid webhook payload', 0, $e);
        }

        if ($event->type === 'checkout.session.completed') {
            /** @var \Stripe\Checkout\Session $session */
            $session = $event->data->object;
            $this->logger->info('Stripe webhook checkout.session.completed', [
                'session_id' => $session->id ?? null,
                'order_id' => $session->metadata->order_id ?? null,
            ]);
            $this->handleCheckoutSessionCompleted($session);
        }
    }

    private function handleCheckoutSessionCompleted(\Stripe\Checkout\Session $session): void
    {
        $orderId = $session->metadata->order_id ?? null;
        if (!$orderId) {
            return;
        }

        $order = $this->entityManager->getRepository(Order::class)->find((int) $orderId);
        if (!$order) {
            return;
        }

        // Sécurité : s'assurer que le webhook correspond bien à la session enregistrée
        if ($order->getStripeSessionId() && $order->getStripeSessionId() !== $session->id) {
            return;
        }

        // Idempotence : si déjà payé, on ne redécrémente pas le stock
        if ($order->getStatus() === OrderStatus::PAID) {
            return;
        }

        $order->setStatus(OrderStatus::PAID);

        foreach ($order->getOrderItems() as $orderItem) {
            $book = $orderItem->getBook();
            $book->decrementStock((int) $orderItem->getQuantity());
        }

        $this->entityManager->flush();
    }

    public function syncPaidOrderFromSession(Session $session): void
    {
        if (($session->payment_status ?? null) !== 'paid') {
            return;
        }

        $this->handleCheckoutSessionCompleted($session);
    }

    public function retrieveSession(string $sessionId): ?Session
    {
        try {
            $session = Session::retrieve($sessionId);
            $this->logger->info('Stripe session retrieved', [
                'session_id' => $sessionId,
                'status' => $session->status ?? null,
                'payment_status' => $session->payment_status ?? null,
            ]);

            return $session;
        } catch (ApiErrorException $e) {
            $this->logger->warning('Stripe session retrieve failed', [
                'session_id' => $sessionId,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
