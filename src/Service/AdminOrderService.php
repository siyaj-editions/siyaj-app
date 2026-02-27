<?php

namespace App\Service;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminOrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EntityManagerInterface $entityManager
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

    private function isAllowedStatus(?string $status): bool
    {
        return $status !== null && in_array($status, ['pending', 'paid', 'canceled', 'refunded'], true);
    }
}
