<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show checkout page
     *
     * Episode 1 BUG: Direct env() usage instead of config()
     * This will break when config:cache is run!
     */
    public function index(CartService $cartService)
    {
        $cart = $cartService->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }

        // ============================================
        // EPISODE 1 BUG: Direct env() call!
        // This returns null after `php artisan config:cache`
        // ============================================
        $taxRate = config('shop.tax_rate');

        // Debug logging to help demonstrate the issue
        Log::debug('CheckoutController: Tax rate from env()', [
            'tax_rate' => $taxRate,
            'env_function_result' => env('TAX_RATE'),
            'config_value' => config('shop.tax_rate'),
        ]);

        $subtotal = $cart->getSubtotal();

        // Episode 10 BUG: Float math for money
        $tax = (int)round($subtotal * $taxRate);
        $total = $subtotal + $tax;

        return view('checkout.index', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'taxRate' => $taxRate,
        ]);
    }

    /**
     * Process checkout
     *
     * Episode 4 BUG: Race condition in inventory check
     * No locking, no transaction for inventory updates
     */
    public function store(Request $request, CartService $cartService)
    {
        $cart = $cartService->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }

        // Episode 8 BUG: Excessive logging with sensitive data
        Log::debug('Processing checkout', [
            'user' => Auth::user()->toArray(), // BUG: Logging entire user object
            'cart_items' => $cart->items->toArray(),
            'request' => $request->all(), // BUG: Could include sensitive data
        ]);

        try {
            $order = DB::transaction(function () use ($cart) {
                $productIds = $cart->items->pluck('product_id')->all();

                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($cart->items as $item) {
                    $product = $products->get($item->product_id);

                    if (!$product || $product->stock_quantity < $item->quantity) {
                        $name = $product?->name ?? 'This product';
                        $available = $product?->stock_quantity ?? 0;

                        throw ValidationException::withMessages([
                            'stock' => "Sorry, {$name} only has {$available} in stock.",
                        ]);
                    }
                }

                // ============================================
                // EPISODE 1 BUG: Direct env() usage
                // ============================================
                $taxRate = config('shop.tax_rate');

                $subtotal = $cart->getSubtotal();
                $tax = (int)round($subtotal * $taxRate);
                $total = $subtotal + $tax;

                // Create the order
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'order_number' => Order::generateOrderNumber(),
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'status' => Order::STATUS_PENDING,
                    'placed_at' => now(),
                ]);

                // Create order items and update inventory
                foreach ($cart->items as $item) {
                    $product = $products->get($item->product_id);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price_at_time,
                        'subtotal' => $item->price_at_time * $item->quantity,
                    ]);

                    $product->stock_quantity -= $item->quantity;
                    $product->save();

                    // Episode 8 BUG: Logging in a loop
                    Log::info("Decreased stock for product {$product->id}", [
                        'product' => $product->name,
                        'quantity_sold' => $item->quantity,
                        'new_stock' => $product->stock_quantity,
                    ]);
                }

                // Clear the cart
                $cart->items()->delete();

                return $order;
            }, 3);
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Episode 8 BUG: More excessive logging
        Log::info('Order completed', [
            'order' => $order->toArray(),
            'user' => Auth::user()->toArray(),
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully!');
    }
}
