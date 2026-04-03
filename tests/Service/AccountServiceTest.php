<?php

namespace App\Tests\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\User;
use App\Repository\AddressRepository;
use App\Repository\OrderRepository;
use App\Service\AccountService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountServiceTest extends TestCase
{
    private function createUser(string $email = 'user@test.com'): User
    {
        return (new User())
            ->setEmail($email)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setNumero('0600000000')
            ->setPassword('hash');
    }

    public function testCreateDefaultAddressForUserPrepopulatesFields(): void
    {
        $orderRepository = $this->createMock(OrderRepository::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $addressRepository
            ->expects(self::once())
            ->method('findDefaultForUser')
            ->willReturn(null);

        $service = new AccountService($orderRepository, $addressRepository, $entityManager);
        $user = $this->createUser();

        $address = $service->createDefaultAddressForUser($user);

        self::assertSame($user, $address->getUser());
        self::assertSame('John', $address->getFirstname());
        self::assertSame('Doe', $address->getLastname());
        self::assertSame('0600000000', $address->getNumero());
        self::assertSame('FR', $address->getCountry());
        self::assertTrue($address->isDefault());
    }

    public function testUpdateProfileHashesPasswordWhenProvided(): void
    {
        $orderRepository = $this->createMock(OrderRepository::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $user = $this->createUser();

        $hasher
            ->expects(self::once())
            ->method('hashPassword')
            ->with($user, 'new-password')
            ->willReturn('new-hash');

        $entityManager->expects(self::once())->method('flush');

        $service = new AccountService($orderRepository, $addressRepository, $entityManager);
        $service->updateProfile($user, 'new-password', $hasher);

        self::assertSame('new-hash', $user->getPassword());
    }

    public function testFindUserOrderByIdReturnsNullForOtherUserOrder(): void
    {
        $orderRepository = $this->createMock(OrderRepository::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $owner = $this->createUser('owner@test.com');
        $other = $this->createUser('other@test.com');

        $order = (new Order())->setUser($owner)->setTotalCents(1000);

        $orderRepository->method('find')->with(42)->willReturn($order);

        $service = new AccountService($orderRepository, $addressRepository, $entityManager);
        self::assertNull($service->findUserOrderById($other, 42));
    }

    public function testSaveAndDeleteAddressFlushPersistence(): void
    {
        $orderRepository = $this->createMock(OrderRepository::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $address = (new Address())
            ->setUser($this->createUser())
            ->setFirstname('A')
            ->setLastname('B')
            ->setStreet('Street')
            ->setPostalCode('75000')
            ->setCity('Paris')
            ->setCountry('France');

        $addressRepository->expects(self::once())
            ->method('findDefaultForUser')
            ->willReturn(null);
        $addressRepository->expects(self::once())
            ->method('findByUser')
            ->willReturn([]);
        $addressRepository->expects(self::once())
            ->method('findLatestForUser')
            ->willReturn(null);

        $entityManager->expects(self::once())->method('persist')->with($address);
        $entityManager->expects(self::once())->method('remove')->with($address);
        $entityManager->expects(self::exactly(2))->method('flush');

        $service = new AccountService($orderRepository, $addressRepository, $entityManager);
        $service->saveAddress($address);
        $service->deleteAddress($address);

        self::assertTrue(true);
    }

    public function testSetDefaultAddressUnsetsPreviousDefault(): void
    {
        $orderRepository = $this->createMock(OrderRepository::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $user = $this->createUser();
        $oldDefault = (new Address())
            ->setUser($user)
            ->setFirstname('Old')
            ->setLastname('Default')
            ->setStreet('Street')
            ->setPostalCode('75000')
            ->setCity('Paris')
            ->setCountry('France')
            ->setIsDefault(true);
        $newDefault = (new Address())
            ->setUser($user)
            ->setFirstname('New')
            ->setLastname('Default')
            ->setStreet('Street')
            ->setPostalCode('97100')
            ->setCity('Basse-Terre')
            ->setCountry('France');

        $addressRepository->expects(self::once())
            ->method('findByUser')
            ->with($user)
            ->willReturn([$oldDefault, $newDefault]);

        $entityManager->expects(self::once())->method('flush');

        $service = new AccountService($orderRepository, $addressRepository, $entityManager);
        $service->setDefaultAddress($user, $newDefault);

        self::assertFalse($oldDefault->isDefault());
        self::assertTrue($newDefault->isDefault());
    }
}
