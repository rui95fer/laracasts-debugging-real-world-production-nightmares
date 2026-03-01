<?php

namespace Tests\Unit;

use App\Services\OrderCalculator;
use App\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class OrderCalculatorTest extends TestCase
{
    public function test_calculate_money_returns_money_objects(): void
    {
        $calculator = new OrderCalculator();

        $totals = $calculator->calculateMoney([
            ['product_id' => 1, 'price' => 1033, 'quantity' => 1],
            ['product_id' => 2, 'price' => 1577, 'quantity' => 2],
            ['product_id' => 3, 'price' => 899, 'quantity' => 1],
        ], 0.08);

        $this->assertInstanceOf(Money::class, $totals['subtotal']);
        $this->assertInstanceOf(Money::class, $totals['tax']);
        $this->assertInstanceOf(Money::class, $totals['total']);

        $this->assertSame(5086, $totals['subtotal']->getCents());
        $this->assertSame(407, $totals['tax']->getCents());
        $this->assertSame(5493, $totals['total']->getCents());
    }

    public function test_calculate_keeps_integer_cent_contract(): void
    {
        $calculator = new OrderCalculator();

        $totals = $calculator->calculate([
            ['product_id' => 1, 'price' => 1033, 'quantity' => 1],
            ['product_id' => 2, 'price' => 1577, 'quantity' => 2],
            ['product_id' => 3, 'price' => 899, 'quantity' => 1],
        ], 0.08);

        $this->assertSame(['subtotal' => 5086, 'tax' => 407, 'total' => 5493], $totals);
    }
}
