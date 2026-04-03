<?php

namespace App\Tests\Service;

use App\Entity\Address;
use App\Service\ShippingService;
use PHPUnit\Framework\TestCase;

class ShippingServiceTest extends TestCase
{
    private function createAddress(string $postalCode, string $country = 'France'): Address
    {
        return (new Address())
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setStreet('1 rue de test')
            ->setPostalCode($postalCode)
            ->setCity('Test City')
            ->setCountry($country);
    }

    public function testMartiniqueSupportsPickupAndDelivery(): void
    {
        $service = new ShippingService();
        $address = $this->createAddress('97200');

        $methods = $service->getAvailableMethods($address);

        self::assertArrayHasKey(ShippingService::METHOD_DELIVERY, $methods);
        self::assertArrayHasKey(ShippingService::METHOD_PICKUP, $methods);

        $pickupQuote = $service->quote($address, 2500, ShippingService::METHOD_PICKUP);
        self::assertSame(0, $pickupQuote->costCents);
        self::assertSame('Retrait en magasin', $pickupQuote->delayLabel);
    }

    public function testGuadeloupeDeliveryIsChargedBelowThreshold(): void
    {
        $service = new ShippingService();
        $address = $this->createAddress('97110');

        $quote = $service->quote($address, 2999, ShippingService::METHOD_DELIVERY);

        self::assertSame('Guadeloupe', $quote->zoneLabel);
        self::assertSame(500, $quote->costCents);
        self::assertSame('2 à 3 jours', $quote->delayLabel);
        self::assertFalse($quote->isFreeShipping);
    }

    public function testEuropeDeliveryIsFreeAtThreshold(): void
    {
        $service = new ShippingService();
        $address = $this->createAddress('75001', 'France');

        $quote = $service->quote($address, 3000, ShippingService::METHOD_DELIVERY);

        self::assertSame('Europe', $quote->zoneLabel);
        self::assertSame(0, $quote->costCents);
        self::assertTrue($quote->isFreeShipping);
        self::assertSame(3000, $quote->totalCents);
    }

    public function testRowUsesFixedRate(): void
    {
        $service = new ShippingService();
        $address = $this->createAddress('10001', 'United States');

        $quote = $service->quote($address, 1500, ShippingService::METHOD_DELIVERY);

        self::assertSame('Reste du monde', $quote->zoneLabel);
        self::assertSame(1000, $quote->costCents);
        self::assertSame('15 à 21 jours', $quote->delayLabel);
        self::assertSame(2500, $quote->totalCents);
    }
}
