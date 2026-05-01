<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderSend;
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
        $address->setCountry('FR');
        $address->setIsDefault($this->addressRepository->findDefaultForUser($user) === null);

        return $address;
    }

    public function saveAddress(Address $address): void
    {
        $user = $address->getUser();
        if ($user && ($address->isDefault() || $this->addressRepository->findDefaultForUser($user) === null)) {
            $this->unsetDefaultAddressForUser($user, $address);
            $address->setIsDefault(true);
        }

        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }

    public function findUserAddressById(User $user, int $id): ?Address
    {
        return $this->addressRepository->findOneByIdAndUser($id, $user);
    }

    public function deleteAddress(Address $address): void
    {
        $user = $address->getUser();
        $wasDefault = $address->isDefault();

        $this->entityManager->remove($address);
        $this->entityManager->flush();

        if (!$wasDefault || !$user) {
            return;
        }

        $fallbackAddress = $this->addressRepository->findLatestForUser($user);
        if (!$fallbackAddress) {
            return;
        }

        $fallbackAddress->setIsDefault(true);
        $this->entityManager->flush();
    }

    public function setDefaultAddress(User $user, Address $address): void
    {
        if ($address->getUser() !== $user) {
            throw new \InvalidArgumentException('Adresse invalide pour cet utilisateur.');
        }

        $this->unsetDefaultAddressForUser($user, $address);
        $address->setIsDefault(true);
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

    /**
     * @return array{
     *     orders: Order[],
     *     totalOrders: int,
     *     currentPage: int,
     *     totalPages: int,
     *     perPage: int
     * }
     */
    public function getPaginatedUserOrders(User $user, int $page, int $perPage = 10): array
    {
        $totalOrders = $this->orderRepository->countByUser($user);
        $totalPages = max(1, (int) ceil($totalOrders / $perPage));
        $currentPage = min(max(1, $page), $totalPages);

        return [
            'orders' => $this->orderRepository->findPaginatedByUser($user, $currentPage, $perPage),
            'totalOrders' => $totalOrders,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
        ];
    }

    public function findUserOrderById(User $user, int $id): ?Order
    {
        $order = $this->orderRepository->find($id);
        if (!$order || $order->getUser() !== $user) {
            return null;
        }

        return $order;
    }

    public function markOrderAsReceived(Order $order): bool
    {
        if ($order->getSendStatus() !== OrderSend::SENT) {
            return false;
        }

        $order->setSendStatus(OrderSend::RECEIVED);
        $this->entityManager->flush();

        return true;
    }

    private function unsetDefaultAddressForUser(User $user, ?Address $except = null): void
    {
        foreach ($this->addressRepository->findByUser($user) as $existingAddress) {
            if ($except && $existingAddress === $except) {
                continue;
            }

            if ($except && $existingAddress->getId() !== null && $existingAddress->getId() === $except->getId()) {
                continue;
            }

            if ($existingAddress->isDefault()) {
                $existingAddress->setIsDefault(false);
            }
        }
    }
}
