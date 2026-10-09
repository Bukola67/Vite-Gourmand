<?php

namespace App\Tests\Service;

use App\Service\DeliveryFeeCalculator;
use PHPUnit\Framework\TestCase;

final class DeliveryFeeCalculatorTest extends TestCase
{
    public function testCalculateReturnsZeroForBordeaux(): void
    {
        $calculator = new DeliveryFeeCalculator();

        $result = $calculator->calculate(true, 0);

        self::assertSame('0.00', $result);
    }

    public function testCalculateReturnsBaseFeeOutsideBordeauxWhenDistanceIsZero(): void
    {
        $calculator = new DeliveryFeeCalculator();

        $result = $calculator->calculate(false, 0);

        self::assertSame('5.00', $result);
    }

    public function testCalculateAddsDistanceFeeOutsideBordeaux(): void
    {
        $calculator = new DeliveryFeeCalculator();

        $result = $calculator->calculate(false, 10000);

        self::assertSame('10.90', $result);
    }

    public function testCalculateRoundsDistanceFeeCorrectly(): void
    {
        $calculator = new DeliveryFeeCalculator();

        $result = $calculator->calculate(false, 1250);

        self::assertSame('5.74', $result);
    }

    public function testCalculateThrowsExceptionForNegativeDistance(): void
    {
        $calculator = new DeliveryFeeCalculator();

        $this->expectException(\InvalidArgumentException::class);

        $calculator->calculate(false, -1);
    }
}