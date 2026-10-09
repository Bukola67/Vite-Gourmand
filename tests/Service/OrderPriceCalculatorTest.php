<?php

namespace App\Tests\Service;

use App\Service\OrderPriceCalculator;
use PHPUnit\Framework\TestCase;

final class OrderPriceCalculatorTest extends TestCase
{
    public function testCalculateWithoutDiscountAndWithoutDelivery(): void
    {
        $calculator = new OrderPriceCalculator();

        $result = $calculator->calculate('25.00', 4, 4, '0.00');

        self::assertSame('100.00', $result['menuPrice']);
        self::assertSame('0.00', $result['deliveryPrice']);
        self::assertSame('0.00', $result['discountAmount']);
        self::assertSame('100.00', $result['totalPrice']);
    }

    public function testCalculateWithDiscountAndWithoutDelivery(): void
    {
        $calculator = new OrderPriceCalculator();

        $result = $calculator->calculate('25.00', 9, 4, '0.00');

        self::assertSame('225.00', $result['menuPrice']);
        self::assertSame('0.00', $result['deliveryPrice']);
        self::assertSame('22.50', $result['discountAmount']);
        self::assertSame('202.50', $result['totalPrice']);
    }

    public function testCalculateWithoutDiscountAndWithDelivery(): void
    {
        $calculator = new OrderPriceCalculator();

        $result = $calculator->calculate('25.00', 4, 4, '10.90');

        self::assertSame('100.00', $result['menuPrice']);
        self::assertSame('10.90', $result['deliveryPrice']);
        self::assertSame('0.00', $result['discountAmount']);
        self::assertSame('110.90', $result['totalPrice']);
    }

    public function testCalculateWithDiscountAndWithDelivery(): void
    {
        $calculator = new OrderPriceCalculator();

        $result = $calculator->calculate('25.00', 9, 4, '10.90');

        self::assertSame('225.00', $result['menuPrice']);
        self::assertSame('10.90', $result['deliveryPrice']);
        self::assertSame('22.50', $result['discountAmount']);
        self::assertSame('213.40', $result['totalPrice']);
    }

    public function testThrowsExceptionWhenPersonCountIsBelowMinimum(): void
    {
        $calculator = new OrderPriceCalculator();

        $this->expectException(\InvalidArgumentException::class);

        $calculator->calculate('25.00', 3, 4, '0.00');
    }

    public function testThrowsExceptionWhenUnitPriceIsInvalid(): void
    {
        $calculator = new OrderPriceCalculator();

        $this->expectException(\InvalidArgumentException::class);

        $calculator->calculate('abc', 4, 4, '0.00');
    }

    public function testThrowsExceptionWhenDeliveryPriceIsInvalid(): void
    {
        $calculator = new OrderPriceCalculator();

        $this->expectException(\InvalidArgumentException::class);

        $calculator->calculate('25.00', 4, 4, 'abc');
    }
}