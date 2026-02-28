<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\OrderRepository;

class CheckoutFlowService
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly OrderRepository $orderRepository,
        private readonly CartService $cartService
    ) {
    }

    /**
     * @return array{stripePublicKey: mixed, sessionId: string, sessionUrl: string|null}
     */
    public function buildStartViewData(string $sessionId, mixed $stripePublicKey): array
    {
        $session = $this->stripeService->retrieveSession($sessionId);

        return [
            'stripePublicKey' => $stripePublicKey,
            'sessionId' => $sessionId,
            'sessionUrl' => $session?->url,
        ];
    }

    public function handleSuccess(string $sessionId, User $user): CheckoutSuccessResult
    {
        $stripeSession = $this->stripeService->retrieveSession($sessionId);
        $stripeSessionFound = $stripeSession !== null;

        if ($stripeSession) {
            $this->stripeService->syncPaidOrderFromSession($stripeSession);
        }

        $order = $this->orderRepository->findByStripeSessionId($sessionId);
        if (!$order || $order->getUser() !== $user) {
            return new CheckoutSuccessResult(null, $stripeSessionFound);
        }

        if ($order->isPaid()) {
            $this->cartService->clear();
        }

        return new CheckoutSuccessResult($order, $stripeSessionFound);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDebugSessionData(string $sessionId): ?array
    {
        $session = $this->stripeService->retrieveSession($sessionId);
        if (!$session) {
            return null;
        }

        return [
            'id' => $session->id ?? null,
            'status' => $session->status ?? null,
            'payment_status' => $session->payment_status ?? null,
            'livemode' => $session->livemode ?? null,
            'expires_at' => $session->expires_at ?? null,
            'url' => $session->url ?? null,
        ];
    }
}
