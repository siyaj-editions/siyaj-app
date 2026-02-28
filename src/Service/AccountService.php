<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\User;
use App\Repository\AddressRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly AddressRepository $addressRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return Order[]
     */
    public function getRecentOrders(User $user, int $limit = 5): array
    {
        return $this->orderRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    /**
     * @return Address[]
     */
    public function getUserAddresses(User $user): array
    {
        return $this->addressRepository->findByUser($user);
    }

    public function createDefaultAddressForUser(User $user): Address
    {
        $address = new Address();
        $address->setUser($user);
        $address->setFirstname($user->getFirstname());
        $address->setLastname($user->getLastname());
        $address->setNumero($user->getNumero());
        $address->setCountry('France');

        return $address;
    }

    public function saveAddress(Address $address): void
    {
        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }

    public function findUserAddressById(User $user, int $id): ?Address
    {
        return $this->addressRepository->findOneByIdAndUser($id, $user);
    }

    public function deleteAddress(Address $address): void
    {
        $this->entityManager->remove($address);
        $this->entityManager->flush();
    }

    public function updateProfile(User $user, ?string $plainPassword, UserPasswordHasherInterface $passwordHasher): void
    {
        if ($plainPassword) {
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        }

        $this->entityManager->flush();
    }

    /**
     * @return Order[]
     */
    public function getUserOrders(User $user): array
    {
        return $this->orderRepository->findByUser($user);
    }

    public function findUserOrderById(User $user, int $id): ?Order
    {
        $order = $this->orderRepository->find($id);
        if (!$order || $order->getUser() !== $user) {
            return null;
        }

        return $order;
    }
}
