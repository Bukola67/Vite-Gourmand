<?php

namespace App\Service;

final class DeliveryFeeCalculator
{
    public function calculate(bool $isBordeaux, int $distanceMeters = 0): string
    {
        if ($isBordeaux) {
            return '0.00';
        }

        if ($distanceMeters < 0) {
            throw new \InvalidArgumentException(
                'La distance ne peut pas être négative.'
            );
        }

        $baseFeeCents = 500;
        $distanceFeeCents = (int) round(($distanceMeters / 1000) * 59);

        $totalCents = $baseFeeCents + $distanceFeeCents;

        return number_format($totalCents / 100, 2, '.', '');
    }
}