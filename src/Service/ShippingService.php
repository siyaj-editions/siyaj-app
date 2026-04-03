<?php

namespace App\Service;

use App\Entity\Address;

class ShippingService
{
    public const METHOD_DELIVERY = 'delivery';
    public const METHOD_PICKUP = 'pickup';

    private const EUROPE_COUNTRIES = [
        'albania', 'allemagne', 'andorre', 'austria', 'autriche', 'belarus', 'belgique', 'belgium',
        'bosnia and herzegovina', 'bosnie herzegovine', 'bulgaria', 'bulgarie', 'croatia', 'croatie',
        'cyprus', 'chypre', 'czech republic', 'czechia', 'republique tcheque', 'danemark', 'denmark',
        'espagne', 'spain', 'estonia', 'estonie', 'finland', 'finlande', 'france', 'greece', 'grece',
        'hungary', 'hongrie', 'iceland', 'islande', 'ireland', 'irlande', 'italia', 'italie', 'italy',
        'kosovo', 'latvia', 'lettonie', 'liechtenstein', 'lithuania', 'lituanie', 'luxembourg',
        'malta', 'malte', 'moldova', 'moldavie', 'monaco', 'montenegro', 'netherlands', 'pays bas',
        'north macedonia', 'macedoine du nord', 'norway', 'norvege', 'pologne', 'poland', 'portugal',
        'romania', 'roumanie', 'san marino', 'serbia', 'serbie', 'slovakia', 'slovaquie', 'slovenia',
        'slovenie', 'suede', 'sweden', 'switzerland', 'suisse', 'uk', 'united kingdom', 'royaume uni',
        'england', 'scotland', 'wales', 'northern ireland', 'ukraine', 'vatican', 'vatican city',
        'fr', 'be', 'de', 'es', 'it', 'pt', 'nl', 'lu', 'ie', 'at', 'gr', 'se', 'fi', 'dk', 'pl',
        'cz', 'ro', 'bg', 'hr', 'hu', 'si', 'sk', 'ee', 'lv', 'lt', 'cy', 'mt',
    ];

    /**
     * @return array<string, string>
     */
    public function getAvailableMethods(Address $address): array
    {
        $zoneCode = $this->resolveZoneCode($address);

        $methods = [
            self::METHOD_DELIVERY => 'Livraison',
        ];

        if ($zoneCode === 'martinique') {
            $methods[self::METHOD_PICKUP] = 'Retrait en magasin';
        }

        return $methods;
    }

    public function getDefaultMethod(Address $address): string
    {
        $methods = $this->getAvailableMethods($address);

        return array_key_first($methods) ?? self::METHOD_DELIVERY;
    }

    public function quote(Address $address, int $itemsSubtotalCents, ?string $methodCode = null): ShippingQuote
    {
        $zoneCode = $this->resolveZoneCode($address);
        $methodCode ??= $this->getDefaultMethod($address);

        $availableMethods = $this->getAvailableMethods($address);
        if (!isset($availableMethods[$methodCode])) {
            throw new \InvalidArgumentException('Mode de livraison invalide pour cette zone.');
        }

        $zoneLabel = $this->getZoneLabel($zoneCode);
        $methodLabel = $availableMethods[$methodCode];
        $costCents = $this->resolveCostCents($zoneCode, $methodCode, $itemsSubtotalCents);
        $delayLabel = $this->resolveDelayLabel($zoneCode, $methodCode);

        return new ShippingQuote(
            zoneCode: $zoneCode,
            zoneLabel: $zoneLabel,
            methodCode: $methodCode,
            methodLabel: $methodLabel,
            costCents: $costCents,
            delayLabel: $delayLabel,
            itemsSubtotalCents: $itemsSubtotalCents,
            totalCents: $itemsSubtotalCents + $costCents,
            isFreeShipping: $methodCode === self::METHOD_DELIVERY && $costCents === 0
        );
    }

    /**
     * @param Address[] $addresses
     * @return array<string, array<string, mixed>>
     */
    public function buildCheckoutPreviewMap(array $addresses, int $itemsSubtotalCents): array
    {
        $previews = [];

        foreach ($addresses as $address) {
            $addressId = $address->getId();
            if ($addressId === null) {
                continue;
            }

            $methods = [];
            foreach ($this->getAvailableMethods($address) as $methodCode => $methodLabel) {
                $quote = $this->quote($address, $itemsSubtotalCents, $methodCode);
                $methods[$methodCode] = [
                    'methodCode' => $quote->methodCode,
                    'methodLabel' => $quote->methodLabel,
                    'costCents' => $quote->costCents,
                    'costFormatted' => $this->formatMoney($quote->costCents),
                    'delayLabel' => $quote->delayLabel,
                    'totalCents' => $quote->totalCents,
                    'totalFormatted' => $this->formatMoney($quote->totalCents),
                    'isFreeShipping' => $quote->isFreeShipping,
                ];
            }

            $defaultMethod = $this->getDefaultMethod($address);
            $previews[(string) $addressId] = [
                'zoneCode' => $this->resolveZoneCode($address),
                'zoneLabel' => $this->getZoneLabel($this->resolveZoneCode($address)),
                'defaultMethod' => $defaultMethod,
                'methods' => $methods,
            ];
        }

        return $previews;
    }

    public function formatMoney(int $amountCents): string
    {
        if ($amountCents === 0) {
            return 'Gratuit';
        }

        return number_format($amountCents / 100, 2, ',', ' ') . ' €';
    }

    private function resolveCostCents(string $zoneCode, string $methodCode, int $itemsSubtotalCents): int
    {
        if ($methodCode === self::METHOD_PICKUP) {
            return 0;
        }

        return match ($zoneCode) {
            'martinique', 'guadeloupe', 'sxm_guyane' => $itemsSubtotalCents >= 3000 ? 0 : 500,
            'europe' => $itemsSubtotalCents >= 3000 ? 0 : 800,
            'row' => 1000,
            default => 1000,
        };
    }

    private function resolveDelayLabel(string $zoneCode, string $methodCode): string
    {
        if ($methodCode === self::METHOD_PICKUP) {
            return 'Retrait en magasin';
        }

        return match ($zoneCode) {
            'martinique' => '5 à 8 jours',
            'guadeloupe' => '2 à 3 jours',
            'sxm_guyane' => '5 à 8 jours',
            'europe' => '8 à 10 jours',
            'row' => '15 à 21 jours',
            default => '15 à 21 jours',
        };
    }

    private function resolveZoneCode(Address $address): string
    {
        $country = $this->normalize($address->getCountry());
        $postalCode = preg_replace('/\s+/', '', (string) $address->getPostalCode());

        if (str_starts_with($postalCode, '972') || $country === 'martinique') {
            return 'martinique';
        }

        if (str_starts_with($postalCode, '97150') || in_array($country, ['saint martin', 'saintmartin', 'sxm', 'sint maarten'], true)) {
            return 'sxm_guyane';
        }

        if (str_starts_with($postalCode, '971') || $country === 'guadeloupe') {
            return 'guadeloupe';
        }

        if (str_starts_with($postalCode, '973') || in_array($country, ['guyane', 'guyane francaise', 'french guiana'], true)) {
            return 'sxm_guyane';
        }

        if (in_array($country, self::EUROPE_COUNTRIES, true)) {
            return 'europe';
        }

        return 'row';
    }

    private function getZoneLabel(string $zoneCode): string
    {
        return match ($zoneCode) {
            'martinique' => 'Martinique',
            'guadeloupe' => 'Guadeloupe',
            'sxm_guyane' => 'SXM / Guyane',
            'europe' => 'Europe',
            'row' => 'Reste du monde',
            default => 'Reste du monde',
        };
    }

    private function normalize(?string $value): string
    {
        $value ??= '';
        $value = trim($value);
        $value = mb_strtolower($value);
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (is_string($transliterated)) {
            $value = $transliterated;
        }

        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
