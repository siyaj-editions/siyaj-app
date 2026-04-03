<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Exception\CheckoutException;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;

class CheckoutService
{
    public function __construct(
        private CartService $cartService,
        private AddressRepository $addressRepository,
        private ShippingService $shippingService,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return list<string>
     */
    public function getCartValidationErrors(): array
    {
        if ($this->cartService->isEmpty()) {
            return ['Votre panier est vide.'];
        }

        return $this->cartService->validateStock();
    }

    /**
     * @return Address[]
     */
    public function getUserAddresses(User $user): array
    {
        return $this->addressRepository->findByUser($user);
    }

    /**
     * @param Address[] $addresses
     * @return array<string, string>
     */
    public function buildAddressChoices(array $addresses): array
    {
        $choices = [];
        foreach ($addresses as $address) {
            $label = sprintf('%s - %s', $address->getFullName(), $address->getInline());
            $choices[$label] = (string) $address->getId();
        }

        return $choices;
    }

    /**
     * @param Address[] $addresses
     */
    public function getDefaultAddressId(array $addresses): ?string
    {
        if ($addresses === []) {
            return null;
        }

        foreach ($addresses as $address) {
            if ($address->isDefault()) {
                return (string) $address->getId();
            }
        }

        return (string) $addresses[0]->getId();
    }

    /**
     * @param Address[] $addresses
     * @return array<string, array<string, string|null>>
     */
    public function buildAddressSummaryMap(array $addresses): array
    {
        $summaries = [];

        foreach ($addresses as $address) {
            $addressId = $address->getId();
            if ($addressId === null) {
                continue;
            }

            $summaries[(string) $addressId] = [
                'fullName' => $address->getFullName(),
                'inline' => $address->getInline(),
                'numero' => $address->getNumero(),
            ];
        }

        return $summaries;
    }

    /**
     * @param Address[] $addresses
     * @return array<string, array<string, mixed>>
     */
    public function buildShippingPreviewMap(array $addresses): array
    {
        return $this->shippingService->buildCheckoutPreviewMap(
            $addresses,
            $this->cartService->getTotalCents()
        );
    }

    public function getDefaultShippingMethod(Address $address): string
    {
        return $this->shippingService->getDefaultMethod($address);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createOrderFromFormData(User $user, array $data): Order
    {
        $billingSameAsShipping = (bool) ($data['billingSameAsShipping'] ?? true);
        $shippingAddressId = (int) ($data['shippingAddressId'] ?? 0);
        $billingAddressId = (int) ($data['billingAddressId'] ?? 0);
        $shippingMethod = is_string($data['shippingMethod'] ?? null)
            ? $data['shippingMethod']
            : null;

        $shippingAddress = $this->addressRepository->findOneByIdAndUser($shippingAddressId, $user);
        if (!$shippingAddress) {
            throw new CheckoutException('Adresse de livraison invalide.');
        }

        $billingAddress = $billingSameAsShipping
            ? $shippingAddress
            : $this->addressRepository->findOneByIdAndUser($billingAddressId, $user);

        if (!$billingAddress) {
            throw new CheckoutException('Adresse de facturation invalide.');
        }

        $cart = $this->cartService->getFullCart();
        if ($cart === []) {
            throw new CheckoutException('Votre panier est vide.');
        }

        $itemsSubtotalCents = 0;
        foreach ($cart as $item) {
            $itemsSubtotalCents += $item['book']->getPriceCents() * $item['quantity'];
        }

        try {
            $shippingQuote = $this->shippingService->quote(
                $shippingAddress,
                $itemsSubtotalCents,
                $shippingMethod
            );
        } catch (\InvalidArgumentException) {
            throw new CheckoutException('Mode de livraison invalide pour l’adresse sélectionnée.');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setCurrency('EUR');
        $order->setShippingAddress($shippingAddress);
        $order->setBillingAddress($billingAddress);
        $order->setBillingSameAsShipping($billingSameAsShipping);
        $order->setShippingZone($shippingQuote->zoneCode);
        $order->setShippingZoneLabel($shippingQuote->zoneLabel);
        $order->setShippingMethod($shippingQuote->methodCode);
        $order->setShippingMethodLabel($shippingQuote->methodLabel);
        $order->setShippingDelayLabel($shippingQuote->delayLabel);
        $order->setShippingCostCents($shippingQuote->costCents);

        foreach ($cart as $item) {
            $orderItem = new OrderItem();
            $orderItem->setBook($item['book']);
            $orderItem->setTitleSnapshot($item['book']->getTitle());
            $orderItem->setPriceSnapshot($item['book']->getPriceCents());
            $orderItem->setQuantity($item['quantity']);
            $order->addOrderItem($orderItem);
        }

        $order->calculateTotal();
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }
}
