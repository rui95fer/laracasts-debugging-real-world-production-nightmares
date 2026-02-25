<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Episode 5: Cache Key Collision
 *
 * This test protects the cart total cache key scoping.
 */
class CacheKeyCollisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_total_cache_is_scoped_per_user(): void
    {
        Cache::flush();

        /** @var User $firstUser */
        $firstUser = User::factory()->create();
        /** @var User $secondUser */
        $secondUser = User::factory()->create();

        $firstProduct = Product::factory()->withPrice(1500)->create();
        $secondProduct = Product::factory()->withPrice(3200)->create();

        $firstCart = Cart::factory()->create(['user_id' => $firstUser->id, 'session_id' => null]);
        $secondCart = Cart::factory()->create(['user_id' => $secondUser->id, 'session_id' => null]);

        CartItem::factory()->create([
            'cart_id' => $firstCart->id,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
            'price_at_time' => $firstProduct->price,
        ]);

        CartItem::factory()->create([
            'cart_id' => $secondCart->id,
            'product_id' => $secondProduct->id,
            'quantity' => 1,
            'price_at_time' => $secondProduct->price,
        ]);

        $service = app(CartService::class);

        /** @var Authenticatable $firstUserAuth */
        $firstUserAuth = $firstUser;
        /** @var Authenticatable $secondUserAuth */
        $secondUserAuth = $secondUser;

        $this->actingAs($firstUserAuth);
        $firstTotal = $service->getCartTotal();

        $this->actingAs($secondUserAuth);
        $secondTotal = $service->getCartTotal();

        $this->assertSame(3000, $firstTotal);
        $this->assertSame(3200, $secondTotal);
        $this->assertNotSame($firstTotal, $secondTotal);
    }
}
