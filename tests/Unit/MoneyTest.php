<?php

namespace Tests\Unit;

use App\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

/**
 * Episode 10: The Silent Money Bug
 * 
 * These tests demonstrate floating-point precision issues
 * and show how the Money value object solves them.
 */
class MoneyTest extends TestCase
{
    /**
     * The classic floating-point problem.
     */
    public function test_floating_point_problem_exists(): void
    {
        // This is the problem we're solving
        $result = 0.1 + 0.2;
        
        // This assertion would FAIL because 0.1 + 0.2 != 0.3 in floats!
        // $this->assertEquals(0.3, $result);
        
        // The actual result is something like 0.30000000000000004
        $this->assertNotEquals(0.3, $result);
    }

    /**
     * Money value object handles this correctly.
     */
    public function test_money_handles_addition_correctly(): void
    {
        $a = Money::fromDollars(0.1);  // 10 cents
        $b = Money::fromDollars(0.2);  // 20 cents
        
        $sum = $a->add($b);
        
        $this->assertEquals(30, $sum->getCents());
        $this->assertEquals(0.30, $sum->toDollars());
    }

    /**
     * Test creating money from cents.
     */
    public function test_create_from_cents(): void
    {
        $money = Money::fromCents(1099);
        
        $this->assertEquals(1099, $money->getCents());
        $this->assertEquals(10.99, $money->toDollars());
        $this->assertEquals('$10.99', $money->format());
    }

    /**
     * Test creating money from dollars.
     */
    public function test_create_from_dollars(): void
    {
        $money = Money::fromDollars(10.99);
        
        $this->assertEquals(1099, $money->getCents());
    }

    /**
     * Test multiplication (for quantity calculations).
     */
    public function test_multiplication(): void
    {
        $price = Money::fromCents(999);  // $9.99
        $quantity = 3;
        
        $total = $price->multiply($quantity);
        
        $this->assertEquals(2997, $total->getCents());  // $29.97
    }

    /**
     * Test percentage (for tax calculations).
     */
    public function test_percentage_for_tax(): void
    {
        $subtotal = Money::fromCents(10000);  // $100.00
        $taxRate = 0.08;  // 8%
        
        $tax = $subtotal->percentage($taxRate);
        
        $this->assertEquals(800, $tax->getCents());  // $8.00
    }

    /**
     * Test edge case: rounding.
     */
    public function test_rounding_on_tax(): void
    {
        // $10.33 with 8% tax
        $subtotal = Money::fromCents(1033);
        $tax = $subtotal->percentage(0.08);
        
        // 1033 * 0.08 = 82.64, rounds to 83
        $this->assertEquals(83, $tax->getCents());
    }

    /**
     * BUG DEMONSTRATION: Floating-point calculation.
     */
    public function test_buggy_float_calculation(): void
    {
        // This simulates the buggy code in CheckoutController
        $prices = [10.33, 15.77, 8.99];
        $quantities = [1, 2, 1];
        
        // Buggy approach (floats)
        $subtotal = 0.0;
        for ($i = 0; $i < count($prices); $i++) {
            $subtotal += $prices[$i] * $quantities[$i];
        }
        
        $tax = $subtotal * 0.08;
        $total = round($subtotal + $tax, 2);
        
        // This MIGHT work or MIGHT not due to float precision
        // The result can vary slightly
        
        // Correct approach (integers)
        $pricesCents = [1033, 1577, 899];
        $subtotalCents = 0;
        for ($i = 0; $i < count($pricesCents); $i++) {
            $subtotalCents += $pricesCents[$i] * $quantities[$i];
        }
        
        $taxCents = (int) round($subtotalCents * 0.08);
        $totalCents = $subtotalCents + $taxCents;
        
        // Integer approach is deterministic
        $this->assertEquals(6686, $subtotalCents);  // $66.86
        $this->assertEquals(535, $taxCents);        // $5.35
        $this->assertEquals(7221, $totalCents);     // $72.21
    }

    /**
     * Test comparison methods.
     */
    public function test_comparisons(): void
    {
        $a = Money::fromCents(1000);
        $b = Money::fromCents(2000);
        $c = Money::fromCents(1000);
        
        $this->assertTrue($a->equals($c));
        $this->assertFalse($a->equals($b));
        $this->assertTrue($b->greaterThan($a));
        $this->assertTrue($a->lessThan($b));
    }

    /**
     * Test zero money.
     */
    public function test_zero(): void
    {
        $zero = Money::zero();
        
        $this->assertEquals(0, $zero->getCents());
        $this->assertTrue($zero->isZeroOrLess());
        
        $positive = Money::fromCents(100);
        $this->assertFalse($positive->isZeroOrLess());
    }
}
