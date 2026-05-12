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

        if ($order->getStatus() === OrderStatus::CANCELED) {
            $order->setSendStatus(OrderSend::CANCELED);
        }

        $this->entityManager->flush();

        return true;
    }

    public function updateOrderSendStatus(Order $order, ?string $sendStatus): string
    {
        if (!$this->isAllowedSendStatus($sendStatus)) {
            return 'invalid';
        }

        $newSendStatus = OrderSend::from($sendStatus);
        $alreadySameStatus = $order->getSendStatus() === $newSendStatus;

        $order->setSendStatus($newSendStatus);
        $this->entityManager->flush();

        if ($alreadySameStatus || $newSendStatus !== OrderSend::READY_FOR_PICKUP) {
            return 'updated';
        }

        try {
            $this->orderNotificationService->sendReadyForPickupNotification($order);
        } catch (\Throwable) {
            return 'email_failed';
        }

        return 'updated';
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

    private function isAllowedSendStatus(?string $sendStatus): bool
    {
        return $sendStatus !== null && in_array($sendStatus, ['processing', 'sent', 'ready_for_pickup', 'received'], true);
    }
}
