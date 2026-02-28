<?php

namespace App\Tests\Service;

use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Service\CartService;
use App\Service\CheckoutFlowService;
use App\Service\StripeService;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;

class CheckoutFlowServiceTest extends TestCase
{
    public function testHandleSuccessReturnsNullOrderWhenNotFound(): void
    {
        $stripeService = $this->createMock(StripeService::class);
        $orderRepository = $this->createMock(OrderRepository::class);
        $cartService = $this->createMock(CartService::class);

        $stripeService->method('retrieveSession')->with('sess_1')->willReturn(null);
        $orderRepository->method('findByStripeSessionId')->with('sess_1')->willReturn(null);
        $cartService->expects(self::never())->method('clear');

        $service = new CheckoutFlowService($stripeService, $orderRepository, $cartService);
        $result = $service->handleSuccess('sess_1', new User());

        self::assertNull($result->order);
        self::assertFalse($result->stripeSessionFound);
    }

    public function testHandleSuccessClearsCartForPaidOrder(): void
    {
        $stripeService = $this->createMock(StripeService::class);
        $orderRepository = $this->createMock(OrderRepository::class);
        $cartService = $this->createMock(CartService::class);

        $user = (new User())->setEmail('user@test.com')->setFirstname('Test')->setLastname('User')->setPassword('hash');
        $order = (new Order())->setUser($user)->setStatus(OrderStatus::PAID)->setTotalCents(1000);

        $stripeSession = Session::constructFrom([
            'id' => 'sess_2',
        ]);

        $stripeService->method('retrieveSession')->with('sess_2')->willReturn($stripeSession);
        $stripeService->expects(self::once())->method('syncPaidOrderFromSession');
        $orderRepository->method('findByStripeSessionId')->with('sess_2')->willReturn($order);
        $cartService->expects(self::once())->method('clear');

        $service = new CheckoutFlowService($stripeService, $orderRepository, $cartService);
        $result = $service->handleSuccess('sess_2', $user);

        self::assertSame($order, $result->order);
        self::assertTrue($result->stripeSessionFound);
    }

    public function testGetDebugSessionDataReturnsMappedPayload(): void
    {
        $stripeService = $this->createMock(StripeService::class);
        $orderRepository = $this->createMock(OrderRepository::class);
        $cartService = $this->createMock(CartService::class);

        $session = Session::constructFrom([
            'id' => 'sess_3',
            'status' => 'open',
            'payment_status' => 'unpaid',
            'livemode' => false,
            'expires_at' => 123456,
            'url' => 'https://stripe.test/sess_3',
        ]);

        $stripeService->method('retrieveSession')->with('sess_3')->willReturn($session);

        $service = new CheckoutFlowService($stripeService, $orderRepository, $cartService);
        $data = $service->getDebugSessionData('sess_3');

        self::assertSame('sess_3', $data['id']);
        self::assertSame('https://stripe.test/sess_3', $data['url']);
    }
}
