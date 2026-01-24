<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Order Calculator Service
 * 
 * Episode 10: The Silent Money Bug
 * 
 * This service demonstrates both the BUGGY and CORRECT ways
 * to calculate order totals. The buggy version uses floating-point
 * math which can cause penny discrepancies.
 */
class OrderCalculator
{
    /**
     * Calculate order totals - BUGGY VERSION
     * 
     * ============================================
     * EPISODE 10 BUG: Floating-point arithmetic
     * ============================================
     * 
     * This uses floats for money calculations, which can
     * cause rounding errors that compound over time.
     */
    public function calculateBuggy(array $items, float $taxRate = 0.08): array
    {
        // BUG: Using float for subtotal
        $subtotal = 0.0;

        foreach ($items as $item) {
            // BUG: Float multiplication
            $itemTotal = ($item['price'] / 100) * $item['quantity'];
            $subtotal += $itemTotal;
            
            // Episode 8 BUG: Logging in loop
            Log::debug("Processing item", [
                'product_id' => $item['product_id'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'item_total' => $itemTotal,
            ]);
        }

        // BUG: Float tax calculation
        $tax = $subtotal * $taxRate;
        
        // BUG: Inconsistent rounding
        $total = round($subtotal + $tax, 2);

        // Convert back to cents (introduces more float errors!)
        return [
            'subtotal' => (int) round($subtotal * 100),
            'tax' => (int) round($tax * 100),
            'total' => (int) round($total * 100),
        ];
    }

    /**
     * Calculate order totals - CORRECT VERSION
     * 
     * This version works entirely in cents (integers)
     * and only rounds once at the very end.
     */
    public function calculate(array $items, float $taxRate = 0.08): array
    {
        // Work in cents throughout
        $subtotalCents = 0;

        foreach ($items as $item) {
            // Integer math - no floating point issues
            $itemTotal = $item['price'] * $item['quantity']; // Both in cents
            $subtotalCents += $itemTotal;
        }

        // Calculate tax in cents with single round
        // (subtotal * rate) with rounding
        $taxCents = (int) round($subtotalCents * $taxRate);
        
        $totalCents = $subtotalCents + $taxCents;

        return [
            'subtotal' => $subtotalCents,
            'tax' => $taxCents,
            'total' => $totalCents,
        ];
    }

    /**
     * Verify order totals match
     * 
     * Used for reconciliation and debugging
     */
    public function verifyOrder(Order $order): array
    {
        $items = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'price' => $item->price,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $taxRate = config('shop.tax_rate', 0.08);
        
        $calculated = $this->calculate($items, $taxRate);
        $calculatedBuggy = $this->calculateBuggy($items, $taxRate);

        return [
            'stored' => [
                'subtotal' => $order->subtotal,
                'tax' => $order->tax,
                'total' => $order->total,
            ],
            'calculated_correct' => $calculated,
            'calculated_buggy' => $calculatedBuggy,
            'matches_correct' => $order->total === $calculated['total'],
            'matches_buggy' => $order->total === $calculatedBuggy['total'],
            'discrepancy' => $order->total - $calculated['total'],
        ];
    }
}
