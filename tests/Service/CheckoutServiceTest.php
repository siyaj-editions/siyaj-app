<?php

namespace App\Tests\Service;

use App\Entity\Address;
use App\Entity\Book;
use App\Entity\User;
use App\Exception\CheckoutException;
use App\Repository\AddressRepository;
use App\Service\CartService;
use App\Service\CheckoutService;
use App\Service\ShippingService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CheckoutServiceTest extends TestCase
{
    private function createUser(): User
    {
        return (new User())
            ->setEmail('user@test.com')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setPassword('hash');
    }

    private function createAddress(User $user, string $street = 'Street'): Address
    {
        return (new Address())
            ->setUser($user)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setStreet($street)
            ->setPostalCode('75000')
            ->setCity('Paris')
            ->setCountry('France');
    }

    public function testGetDefaultAddressIdPrefersDefaultAddress(): void
    {
        $cartService = $this->createMock(CartService::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $shippingService = new ShippingService();

        $user = $this->createUser();
        $first = $this->createAddress($user, 'First');
        $second = $this->createAddress($user, 'Second')->setIsDefault(true);

        $reflection = new \ReflectionProperty(Address::class, 'id');
        $reflection->setValue($first, 1);
        $reflection->setValue($second, 2);

        $service = new CheckoutService($cartService, $addressRepository, $shippingService, $entityManager);

        self::assertSame('2', $service->getDefaultAddressId([$first, $second]));
    }

    public function testBuildAddressSummaryMapReturnsReadableAddressData(): void
    {
        $cartService = $this->createMock(CartService::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $shippingService = new ShippingService();

        $user = $this->createUser();
        $address = $this->createAddress($user, '10 rue Victor Hugo')->setNumero('0600000000');

        $reflection = new \ReflectionProperty(Address::class, 'id');
        $reflection->setValue($address, 7);

        $service = new CheckoutService($cartService, $addressRepository, $shippingService, $entityManager);
        $summaries = $service->buildAddressSummaryMap([$address]);

        self::assertSame('John Doe', $summaries['7']['fullName']);
        self::assertStringContainsString('10 rue Victor Hugo', (string) $summaries['7']['inline']);
        self::assertSame('0600000000', $summaries['7']['numero']);
    }

    public function testGetCartValidationErrorsReturnsEmptyCartMessage(): void
    {
        $cartService = $this->createMock(CartService::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $shippingService = new ShippingService();

        $cartService->method('isEmpty')->willReturn(true);

        $service = new CheckoutService($cartService, $addressRepository, $shippingService, $entityManager);
        self::assertSame(['Votre panier est vide.'], $service->getCartValidationErrors());
    }

    public function testCreateOrderFromFormDataThrowsWhenShippingAddressIsInvalid(): void
    {
        $cartService = $this->createMock(CartService::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $shippingService = new ShippingService();

        $user = $this->createUser();

        $addressRepository
            ->method('findOneByIdAndUser')
            ->with(1, $user)
            ->willReturn(null);

        $service = new CheckoutService($cartService, $addressRepository, $shippingService, $entityManager);

        $this->expectException(CheckoutException::class);
        $this->expectExceptionMessage('Adresse de livraison invalide.');

        $service->createOrderFromFormData($user, [
            'shippingAddressId' => 1,
            'billingSameAsShipping' => true,
        ]);
    }

    public function testCreateOrderFromFormDataThrowsWhenCartIsEmpty(): void
    {
        $cartService = $this->createMock(CartService::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $shippingService = new ShippingService();

        $user = $this->createUser();
        $shipping = $this->createAddress($user);

        $addressRepository
            ->method('findOneByIdAndUser')
            ->with(1, $user)
            ->willReturn($shipping);

        $cartService->method('getFullCart')->willReturn([]);

        $service = new CheckoutService($cartService, $addressRepository, $shippingService, $entityManager);

        $this->expectException(CheckoutException::class);
        $this->expectExceptionMessage('Votre panier est vide.');

        $service->createOrderFromFormData($user, [
            'shippingAddressId' => 1,
            'billingSameAsShipping' => true,
        ]);
    }

    public function testCreateOrderFromFormDataBuildsAndPersistsOrder(): void
    {
        $cartService = $this->createMock(CartService::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $shippingService = new ShippingService();

        $user = $this->createUser();
        $shipping = $this->createAddress($user, 'Shipping street');
        $billing = $this->createAddress($user, 'Billing street');

        $book = (new Book())
            ->setTitle('Book 1')
            ->setPriceCents(1599)
            ->setIsActive(true)
            ->setStock(10)
            ->setSlug('book-1');

        $addressRepository
            ->expects(self::exactly(2))
            ->method('findOneByIdAndUser')
            ->willReturnMap([
                [1, $user, $shipping],
                [2, $user, $billing],
            ]);

        $cartService->method('getFullCart')->willReturn([
            ['book' => $book, 'quantity' => 2],
        ]);

        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $service = new CheckoutService($cartService, $addressRepository, $shippingService, $entityManager);

        $order = $service->createOrderFromFormData($user, [
            'shippingAddressId' => 1,
            'shippingMethod' => ShippingService::METHOD_DELIVERY,
            'billingSameAsShipping' => false,
            'billingAddressId' => 2,
        ]);

        self::assertSame($user, $order->getUser());
        self::assertSame($shipping, $order->getShippingAddress());
        self::assertSame($billing, $order->getBillingAddress());
        self::assertFalse($order->isBillingSameAsShipping());
        self::assertSame('Europe', $order->getShippingZoneLabel());
        self::assertSame('Livraison', $order->getShippingMethodLabel());
        self::assertSame('8 à 10 jours', $order->getShippingDelayLabel());
        self::assertSame(3198, $order->getItemsSubtotalCents());
        self::assertSame(0, $order->getShippingCostCents());
        self::assertSame(3198, $order->getTotalCents());
        self::assertCount(1, $order->getOrderItems());
    }
}
