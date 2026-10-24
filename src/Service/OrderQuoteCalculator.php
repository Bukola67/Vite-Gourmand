<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use App\Entity\Menu;

final class OrderQuoteCalculator
{
    public function __construct(
        private DeliveryDistanceService $distanceService,
        private DeliveryFeeCalculator $deliveryFeeCalculator,
        private OrderPriceCalculator $priceCalculator,
    ) {
    }

    /** @return array{menuPrice: string, deliveryPrice: string, discountAmount: string, totalPrice: string} */
    public function calculate(Menu $menu, CustomerOrder $order): array
    {
        $distance = $this->distanceService->getDistanceInMeters(
            (string) $order->getDeliveryAddress(),
            (string) $order->getDeliveryPostalCode(),
            (string) $order->getDeliveryCity(),
        );
        // Même règle que le contrôleur existant : aucun changement de distance.
        $deliveryPrice = $this->deliveryFeeCalculator->calculate($distance === 0, $distance);

        return $this->priceCalculator->calculate(
            (string) $menu->getBasePrice(),
            (int) $order->getPersonCount(),
            (int) $menu->getMinimumPersons(),
            $deliveryPrice,
        );
    }
}
