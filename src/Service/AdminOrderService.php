<?php

namespace App\Service;

use App\Entity\Order;
use App\Enum\OrderSend;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminOrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderNotificationService $orderNotificationService,
    ) {
    }

    /**
     * @return Order[]
     */
    public function listOrders(): array
    {
        return $this->orderRepository->findBy([], ['createdAt' => 'DESC']);
    }

    public function updateOrderStatus(Order $order, ?string $status): bool
    {
        if (!$this->isAllowedStatus($status)) {
            return false;
        }

        $order->setStatus(OrderStatus::from($status));
        $this->entityManager->flush();

        return true;
    }

    public function updateTrackingNumber(Order $order, ?string $trackingNumber): string
    {
        $normalizedTrackingNumber = $trackingNumber !== null ? trim($trackingNumber) : null;

        if ($normalizedTrackingNumber === null || $normalizedTrackingNumber === '') {
            return 'invalid';
        }

        $alreadySameTracking = $order->getTrackingNumber() === $normalizedTrackingNumber && $order->getSendStatus() === OrderSend::SENT;

        $order
            ->setTrackingNumber($normalizedTrackingNumber)
            ->setSendStatus(OrderSend::SENT);

        $this->entityManager->flush();

        if ($alreadySameTracking) {
            return 'updated';
        }

        try {
            $this->orderNotificationService->sendShipmentNotification($order);
        } catch (\Throwable) {
            return 'email_failed';
        }

        return 'updated';
    }

    private function isAllowedStatus(?string $status): bool
    {
        return $status !== null && in_array($status, ['pending', 'paid', 'canceled', 'refunded'], true);
    }
}
