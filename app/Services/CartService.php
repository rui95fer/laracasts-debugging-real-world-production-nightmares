<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CartService
{
    /**
     * Get or create cart for current user/session
     */
    public function getCart(): Cart
    {
        if (Auth::check()) {
            return $this->getUserCart();
        }

        return $this->getGuestCart();
    }

    /**
     * Get cart for authenticated user
     */
    protected function getUserCart(): Cart
    {
        $cart = Cart::firstOrCreate(
            ['user_id' => Auth::id()],
            ['session_id' => null]
        );

        // Load items with products
        $cart->load('items.product');

        return $cart;
    }

    /**
     * Get cart for guest user
     */
    protected function getGuestCart(): Cart
    {
        $sessionId = session()->getId();

        $cart = Cart::firstOrCreate(
            ['session_id' => $sessionId],
            ['user_id' => null]
        );

        $cart->load('items.product');

        return $cart;
    }

    /**
     * Add item to cart
     */
    public function addItem(Product $product, int $quantity = 1): CartItem
    {
        $cart = $this->getCart();

        // Check if product already in cart
        $existingItem = $cart->items()->where('product_id', $product->id)->first();

        if ($existingItem) {
            $existingItem->quantity += $quantity;
            $existingItem->save();
            
            // Episode 5: Clear cache when cart changes
            // BUG: The cache key isn't properly scoped!
            $this->clearCartCache();
            
            return $existingItem;
        }

        $item = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price_at_time' => $product->price,
        ]);

        // Episode 5: Clear cache
        $this->clearCartCache();

        return $item;
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(int $itemId, int $quantity): void
    {
        $cart = $this->getCart();
        $item = $cart->items()->findOrFail($itemId);

        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->quantity = $quantity;
            $item->save();
        }

        // Episode 5: Clear cache
        $this->clearCartCache();
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $itemId): void
    {
        $cart = $this->getCart();
        $cart->items()->where('id', $itemId)->delete();

        // Episode 5: Clear cache
        $this->clearCartCache();
    }

    /**
     * Clear entire cart
     */
    public function clearCart(): void
    {
        $cart = $this->getCart();
        $cart->items()->delete();

        // Episode 5: Clear cache
        $this->clearCartCache();
    }

    /**
     * Get cart total with caching
     * 
     * ============================================
     * EPISODE 5 BUG: Cache Key Not Scoped to User!
     * ============================================
     * 
     * This caches the cart total with a global key 'cart_total'
     * When User A views their cart, it caches THEIR total
     * When User B views their cart, they see User A's cached total!
     */
    public function getCartTotal(): int
    {
        // ============================================
        // BUG: Global cache key - not scoped to user!
        // ============================================
        return Cache::remember('cart_total', 3600, function () {
            $cart = $this->getCart();
            
            // Episode 8 BUG: Logging cart access
            Log::debug('Calculating cart total', [
                'cart_id' => $cart->id,
                'user_id' => Auth::id(),
            ]);
            
            return $cart->getSubtotal();
        });

        // ============================================
        // CORRECT implementation would be:
        // 
        // $userId = Auth::id() ?? 'guest_' . session()->getId();
        // return Cache::remember("cart_total_{$userId}", 3600, function () {
        //     return $this->getCart()->getSubtotal();
        // });
        // ============================================
    }

    /**
     * Clear cart cache
     * 
     * Episode 5: This also has the bug - clears global key
     */
    protected function clearCartCache(): void
    {
        // BUG: Only clears global key, not user-specific
        Cache::forget('cart_total');
        
        // CORRECT would be:
        // $userId = Auth::id() ?? 'guest_' . session()->getId();
        // Cache::forget("cart_total_{$userId}");
    }

    /**
     * Get cart item count
     */
    public function getItemCount(): int
    {
        // ============================================
        // EPISODE 5 BUG: Another unscoped cache key!
        // ============================================
        return Cache::remember('cart_item_count', 3600, function () {
            return $this->getCart()->getItemCount();
        });
    }

    /**
     * Transfer guest cart to user after login
     */
    public function transferGuestCart(int $userId): void
    {
        $sessionId = session()->getId();
        $guestCart = Cart::where('session_id', $sessionId)->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = Cart::firstOrCreate(
            ['user_id' => $userId],
            ['session_id' => null]
        );

        // Move items from guest cart to user cart
        foreach ($guestCart->items as $item) {
            $existingItem = $userCart->items()
                ->where('product_id', $item->product_id)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $item->quantity;
                $existingItem->save();
            } else {
                $item->cart_id = $userCart->id;
                $item->save();
            }
        }

        // Delete empty guest cart
        $guestCart->delete();

        // Clear cache
        $this->clearCartCache();
    }
}
