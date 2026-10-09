<?php

namespace App\Service;

use App\Service\GeocoderInterface;

final class DeliveryDistanceService
{
    private const ORIGIN_ADDRESS = '12 rue de la cuisine, 33000 Bordeaux';

    public function __construct(
        private GeocoderInterface $geocoder
    ) {
    }

    public function getDistanceInMeters(
        string $deliveryAddress,
        string $deliveryPostalCode,
        string $deliveryCity
    ): int {
        $city = mb_strtolower(trim($deliveryCity));

        if ($city === 'bordeaux') {
            return 0;
        }

        $origin = $this->geocoder->geocode(self::ORIGIN_ADDRESS);

        $destinationAddress = trim(sprintf(
            '%s, %s %s',
            $deliveryAddress,
            $deliveryPostalCode,
            $deliveryCity
        ));

        $destination = $this->geocoder->geocode($destinationAddress);

        return $this->haversineDistanceInMeters(
            $origin['latitude'],
            $origin['longitude'],
            $destination['latitude'],
            $destination['longitude']
        );
    }

    private function haversineDistanceInMeters(
        float $latitudeFrom,
        float $longitudeFrom,
        float $latitudeTo,
        float $longitudeTo
    ): int {
        $earthRadius = 6371000;

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2
        ));

        return (int) round($earthRadius * $angle);
    }
}