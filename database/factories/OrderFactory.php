<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate subtotal in cents
        $subtotal = fake()->numberBetween(1000, 50000); // $10 - $500
        
        // Episode 10: Tax calculation (8%)
        $tax = (int) round($subtotal * 0.08);
        $total = $subtotal + $tax;

        // Episode 7: Orders placed at various times for timezone testing
        $placedAt = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'user_id' => User::factory(),
            'order_number' => Order::generateOrderNumber(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => fake()->randomElement(['pending', 'processing', 'completed']),
            'placed_at' => $placedAt,
        ];
    }

    /**
     * Order with specific status
     */
    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Pending order
     */
    public function pending(): static
    {
        return $this->status('pending');
    }

    /**
     * Processing order
     */
    public function processing(): static
    {
        return $this->status('processing');
    }

    /**
     * Completed order
     */
    public function completed(): static
    {
        return $this->status('completed');
    }

    /**
     * Refunded order
     */
    public function refunded(): static
    {
        return $this->status('refunded');
    }

    /**
     * Order placed today
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'placed_at' => now(),
        ]);
    }

    /**
     * Order placed on specific date
     */
    public function placedOn(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'placed_at' => $date,
        ]);
    }
}
