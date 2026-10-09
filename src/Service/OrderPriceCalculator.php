<?php

namespace App\Service;

final class OrderPriceCalculator
{
    /**
     * @return array{
     *     menuPrice: string,
     *     deliveryPrice: string,
     *     discountAmount: string,
     *     totalPrice: string
     * }
     */
    public function calculate(
        string $unitPrice,
        int $personCount,
        int $minimumPersons,
        string $deliveryPrice
    ): array {
        if ($personCount < $minimumPersons) {
            throw new \InvalidArgumentException(
                sprintf(
                    'La commande nécessite au minimum %d personnes.',
                    $minimumPersons
                )
            );
        }

        $unitPriceCents = $this->eurosToCents($unitPrice);
        $deliveryPriceCents = $this->eurosToCents($deliveryPrice);

        $menuPriceCents = $unitPriceCents * $personCount;

        $discountCents = 0;
        if ($personCount >= $minimumPersons + 5) {
            $discountCents = (int) round($menuPriceCents * 0.10);
        }

        $totalPriceCents = $menuPriceCents - $discountCents + $deliveryPriceCents;

        return [
            'menuPrice' => $this->centsToEuros($menuPriceCents),
            'deliveryPrice' => $this->centsToEuros($deliveryPriceCents),
            'discountAmount' => $this->centsToEuros($discountCents),
            'totalPrice' => $this->centsToEuros($totalPriceCents),
        ];
    }

    private function eurosToCents(string $amount): int
    {
        $normalized = str_replace(',', '.', trim($amount));

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            throw new \InvalidArgumentException(
                sprintf('Le montant "%s" est invalide.', $amount)
            );
        }

        [$euros, $decimals] = array_pad(
            explode('.', $normalized, 2),
            2,
            '0'
        );

        $decimals = str_pad($decimals, 2, '0');

        return ((int) $euros * 100) + (int) $decimals;
    }

    private function centsToEuros(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}