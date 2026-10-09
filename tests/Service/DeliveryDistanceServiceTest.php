<?php

namespace App\Tests\Service;

use App\Service\DeliveryDistanceService;
use App\Service\GeocoderInterface;
use PHPUnit\Framework\TestCase;

final class DeliveryDistanceServiceTest extends TestCase
{
    public function testReturnsZeroForBordeaux(): void
    {
        $geocoder = $this->createStub(GeocoderInterface::class);

        $service = new DeliveryDistanceService($geocoder);

        $distance = $service->getDistanceInMeters(
            '10 rue des Tests',
            '33000',
            'Bordeaux'
        );

        self::assertSame(0, $distance);
    }

    public function testCalculatesDistanceOutsideBordeaux(): void
    {
        $geocoder = $this->createMock(GeocoderInterface::class);

        $geocoder->expects($this->exactly(2))
            ->method('geocode')
            ->willReturnOnConsecutiveCalls(
                [
                    'latitude' => 44.8378,
                    'longitude' => -0.5792,
                ],
                [
                    'latitude' => 44.8850,
                    'longitude' => -0.6450,
                ]
            );

        $service = new DeliveryDistanceService($geocoder);

        $distance = $service->getDistanceInMeters(
            '1 avenue du Test',
            '33700',
            'Mérignac'
        );

        self::assertGreaterThan(0, $distance);
    }
}