<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * NightmareMart Database Seeder
 * 
 * Creates realistic data volumes for testing each episode's bug:
 * - Episode 2: 5,000+ orders for N+1 query demonstration
 * - Episode 4: Low-stock products for race conditions
 * - Episode 6: Large datasets for memory leak testing
 * - Episode 7: Users in multiple timezones
 * - Episode 9: 10,000+ products for search performance issues
 */
class NightmareMartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌙 Seeding NightmareMart database...');

        // Create admin user
        $this->createAdminUser();

        // Create test users in different timezones
        $this->createUsersWithTimezones();

        // Create categories
        $categories = $this->createCategories();

        // Create products (many for search testing)
        $this->createProducts($categories);

        // Create orders (many for N+1 and memory testing)
        $this->createOrders();

        $this->command->info('✅ NightmareMart seeding complete!');
    }

    /**
     * Create admin user
     */
    protected function createAdminUser(): void
    {
        $this->command->info('Creating admin user...');

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@nightmaremart.test',
            'password' => Hash::make('password'),
            'timezone' => 'UTC',
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Also create a regular test user
        User::create([
            'name' => 'Test User',
            'email' => 'user@nightmaremart.test',
            'password' => Hash::make('password'),
            'timezone' => 'America/New_York',
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Create users in different timezones for Episode 7
     */
    protected function createUsersWithTimezones(): void
    {
        $this->command->info('Creating users in different timezones...');

        $timezones = [
            'America/New_York' => 'New York User',
            'America/Los_Angeles' => 'LA User',
            'Europe/London' => 'London User',
            'Europe/Paris' => 'Paris User',
            'Asia/Tokyo' => 'Tokyo User',
            'Asia/Singapore' => 'Singapore User',
            'Australia/Sydney' => 'Sydney User',
        ];

        foreach ($timezones as $tz => $name) {
            User::factory()->inTimezone($tz)->create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@nightmaremart.test',
            ]);
        }

        // Create additional random users
        User::factory()->count(50)->create();
    }

    /**
     * Create product categories
     */
    protected function createCategories(): array
    {
        $this->command->info('Creating categories...');

        $categoryNames = [
            'Electronics' => 'Gadgets, devices, and tech accessories',
            'Clothing' => 'Shirts, pants, jackets, and accessories',
            'Home & Garden' => 'Furniture, decor, and outdoor items',
            'Sports & Outdoors' => 'Athletic gear and outdoor equipment',
            'Books & Media' => 'Books, movies, and music',
            'Toys & Games' => 'Toys, board games, and puzzles',
            'Health & Beauty' => 'Personal care and wellness products',
            'Automotive' => 'Car accessories and parts',
            'Office Supplies' => 'Desk accessories and stationery',
            'Food & Grocery' => 'Snacks, beverages, and pantry items',
        ];

        $categories = [];
        foreach ($categoryNames as $name => $description) {
            $categories[] = Category::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'description' => $description,
            ]);
        }

        return $categories;
    }

    /**
     * Create products (10,000+ for Episode 9 search testing)
     */
    protected function createProducts(array $categories): void
    {
        $this->command->info('Creating products (this may take a minute)...');

        $productCount = 10000;
        $bar = $this->command->getOutput()->createProgressBar($productCount);

        // Create products in batches
        $batchSize = 500;
        for ($i = 0; $i < $productCount; $i += $batchSize) {
            $products = [];
            
            for ($j = 0; $j < $batchSize && ($i + $j) < $productCount; $j++) {
                $category = $categories[array_rand($categories)];
                
                // Episode 4: Some products with very low stock
                $stockLevels = [0, 1, 1, 2, 2, 5, 10, 25, 50, 100, 250, 500];
                $stock = $stockLevels[array_rand($stockLevels)];
                
                // Episode 10: Various price points
                $prices = [499, 999, 1299, 1499, 1999, 2499, 2999, 3999, 4999, 7999, 9999, 14999, 19999, 24999];
                $price = $prices[array_rand($prices)];

                $adjectives = ['Premium', 'Deluxe', 'Classic', 'Modern', 'Pro', 'Basic', 'Ultra', 'Mega', 'Super', 'Elite'];
                $items = ['Widget', 'Gadget', 'Tool', 'Kit', 'Device', 'System', 'Pack', 'Set', 'Unit', 'Item'];
                $name = $adjectives[array_rand($adjectives)] . ' ' . $items[array_rand($items)] . ' ' . ($i + $j + 1);

                $products[] = [
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => \Illuminate\Support\Str::slug($name) . '-' . \Illuminate\Support\Str::random(6),
                    'description' => fake()->paragraphs(2, true),
                    'price' => $price,
                    'stock_quantity' => $stock,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Product::insert($products);
            $bar->advance($batchSize);
        }

        $bar->finish();
        $this->command->newLine();
    }

    /**
     * Create orders (5,000+ for Episode 2 N+1 and Episode 6 memory testing)
     */
    protected function createOrders(): void
    {
        $this->command->info('Creating orders (this may take a few minutes)...');

        $orderCount = 5000;
        $users = User::all();
        $products = Product::all();
        
        $bar = $this->command->getOutput()->createProgressBar($orderCount);

        // Create orders in batches
        $batchSize = 100;
        
        for ($i = 0; $i < $orderCount; $i += $batchSize) {
            for ($j = 0; $j < $batchSize && ($i + $j) < $orderCount; $j++) {
                $user = $users->random();
                
                // Random date in last 6 months for Episode 7 timezone testing
                $placedAt = fake()->dateTimeBetween('-6 months', 'now');
                
                // Generate order items
                $itemCount = fake()->numberBetween(1, 5);
                $orderProducts = $products->random($itemCount);
                
                $subtotal = 0;
                $orderItems = [];
                
                foreach ($orderProducts as $product) {
                    $quantity = fake()->numberBetween(1, 3);
                    $itemSubtotal = $product->price * $quantity;
                    $subtotal += $itemSubtotal;
                    
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'subtotal' => $itemSubtotal,
                    ];
                }
                
                // Episode 10: Tax calculation
                $tax = (int) round($subtotal * 0.08);
                $total = $subtotal + $tax;
                
                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => Order::generateOrderNumber(),
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'status' => fake()->randomElement(['pending', 'processing', 'completed', 'completed', 'completed']),
                    'placed_at' => $placedAt,
                ]);
                
                // Create order items
                foreach ($orderItems as $item) {
                    $item['order_id'] = $order->id;
                    $item['created_at'] = $placedAt;
                    $item['updated_at'] = $placedAt;
                    OrderItem::create($item);
                }
            }
            
            $bar->advance($batchSize);
        }

        $bar->finish();
        $this->command->newLine();
    }
}
