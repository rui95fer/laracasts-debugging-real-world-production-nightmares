<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the cart
     */
    public function index()
    {
        $cart = $this->cartService->getCart();
        
        // Episode 5 BUG: Getting cart total from cached service
        // The cache key isn't scoped to user!
        $cartTotal = $this->cartService->getCartTotal();
        
        return view('cart.index', [
            'cart' => $cart,
            'cartTotal' => $cartTotal,
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $quantity = $request->input('quantity', 1);

        // Check stock (but this isn't atomic - Episode 4 related)
        if ($product->stock_quantity < $quantity) {
            return back()->with('error', 'Not enough stock available.');
        }

        $this->cartService->addItem($product, $quantity);

        return redirect()->route('cart.index')
            ->with('success', "{$product->name} added to cart!");
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, int $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $quantity = $request->input('quantity');

        if ($quantity === 0) {
            $this->cartService->removeItem($itemId);
            return back()->with('success', 'Item removed from cart.');
        }

        $this->cartService->updateQuantity($itemId, $quantity);

        return back()->with('success', 'Cart updated.');
    }

    /**
     * Remove item from cart
     */
    public function remove(int $itemId)
    {
        $this->cartService->removeItem($itemId);

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        $this->cartService->clearCart();

        return redirect()->route('cart.index')
            ->with('success', 'Cart cleared.');
    }
}
