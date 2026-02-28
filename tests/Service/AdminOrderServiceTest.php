<?php

namespace App\Tests\Service;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Service\AdminOrderService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AdminOrderServiceTest extends TestCase
{
    public function testListOrdersDelegatesToRepository(): void
    {
        $orderRepository = $this->createMock(OrderRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $orders = [new Order()];
        $orderRepository
            ->expects(self::once())
            ->method('findBy')
            ->with([], ['createdAt' => 'DESC'])
            ->willReturn($orders);

        $service = new AdminOrderService($orderRepository, $entityManager);
        self::assertSame($orders, $service->listOrders());
    }

    public function testUpdateOrderStatusReturnsFalseForInvalidStatus(): void
    {
        $orderRepository = $this->createMock(OrderRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        $order = new Order();
        $service = new AdminOrderService($orderRepository, $entityManager);

        self::assertFalse($service->updateOrderStatus($order, 'invalid'));
        self::assertSame(OrderStatus::PENDING, $order->getStatus());
    }

    public function testUpdateOrderStatusUpdatesOrderAndFlushes(): void
    {
        $orderRepository = $this->createMock(OrderRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $order = new Order();
        $service = new AdminOrderService($orderRepository, $entityManager);

        self::assertTrue($service->updateOrderStatus($order, 'paid'));
        self::assertSame(OrderStatus::PAID, $order->getStatus());
    }
}
