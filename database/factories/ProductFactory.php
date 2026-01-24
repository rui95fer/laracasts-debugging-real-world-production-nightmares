<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Product name components for realistic names
     */
    protected array $adjectives = [
        'Premium', 'Deluxe', 'Classic', 'Modern', 'Vintage', 'Professional',
        'Essential', 'Ultimate', 'Elite', 'Standard', 'Advanced', 'Basic',
        'Luxury', 'Compact', 'Portable', 'Heavy-Duty', 'Lightweight', 'Eco-Friendly'
    ];

    protected array $products = [
        'Widget', 'Gadget', 'Device', 'Tool', 'Kit', 'Set', 'Pack',
        'System', 'Unit', 'Module', 'Component', 'Accessory', 'Adapter',
        'Cable', 'Holder', 'Stand', 'Case', 'Cover', 'Bag', 'Box',
        'Shirt', 'Pants', 'Jacket', 'Shoes', 'Hat', 'Watch', 'Ring',
        'Lamp', 'Chair', 'Desk', 'Table', 'Shelf', 'Cabinet', 'Mirror'
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $adjective = fake()->randomElement($this->adjectives);
        $product = fake()->randomElement($this->products);
        $name = "{$adjective} {$product}";

        // Episode 10: Price in cents
        // Generate realistic prices like $9.99, $24.99, etc.
        $basePrices = [499, 999, 1499, 1999, 2499, 2999, 3999, 4999, 7999, 9999, 14999, 19999];
        $price = fake()->randomElement($basePrices);

        // Episode 4: Varying stock levels
        // Some products with very low stock to trigger race conditions
        $stockLevels = [
            0,   // Out of stock
            1,   // Race condition trigger!
            2,   // Race condition trigger!
            5,
            10,
            25,
            50,
            100,
            250,
            500,
        ];

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(6),
            'description' => fake()->paragraphs(2, true),
            'price' => $price,
            'stock_quantity' => fake()->randomElement($stockLevels),
            'image_path' => null,
        ];
    }

    /**
     * Set specific stock quantity
     */
    public function withStock(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $quantity,
        ]);
    }

    /**
     * Create with low stock (for Episode 4 race condition testing)
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * Create out of stock product
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Set specific price in cents
     */
    public function withPrice(int $cents): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $cents,
        ]);
    }
}
