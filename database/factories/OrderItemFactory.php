<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $price = fake()->randomElement([499, 999, 1499, 1999, 2499, 2999, 3999, 4999]);
        $subtotal = $price * $quantity;

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * Set specific quantity
     */
    public function quantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            return [
                'quantity' => $quantity,
                'subtotal' => $attributes['price'] * $quantity,
            ];
        });
    }

    /**
     * Set specific price
     */
    public function price(int $cents): static
    {
        return $this->state(function (array $attributes) use ($cents) {
            return [
                'price' => $cents,
                'subtotal' => $cents * $attributes['quantity'],
            ];
        });
    }
}
