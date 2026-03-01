<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\OrderCalculator;
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
    public function index(CartService $cartService, OrderCalculator $orderCalculator)
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
        $taxRate = (float) config('shop.tax_rate', 0.08);

        // Debug logging to help demonstrate the issue
        Log::debug('CheckoutController: Tax rate from env()', [
            'tax_rate' => $taxRate,
            'env_function_result' => env('TAX_RATE'),
            'config_value' => config('shop.tax_rate'),
        ]);

        $totals = $orderCalculator->calculateMoney(
            $this->buildCalculationItems($cart),
            $taxRate,
        );

        return view('checkout.index', [
            'cart' => $cart,
            'subtotal' => $totals['subtotal']->getCents(),
            'tax' => $totals['tax']->getCents(),
            'total' => $totals['total']->getCents(),
            'taxRate' => $taxRate,
        ]);
    }

    /**
     * Process checkout
     *
     * Episode 4 BUG: Race condition in inventory check
     * No locking, no transaction for inventory updates
     */
    public function store(Request $request, CartService $cartService, OrderCalculator $orderCalculator)
    {
        $cart = $cartService->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }

        // Episode 8 BUG: Excessive logging with sensitive data
        Log::debug('Processing checkout', [
            'user_id' => Auth::id(),
            'cart_items' => $cart->items->toArray(),
            'request' => $request->all(), // BUG: Could include sensitive data
        ]);

        try {
            $order = DB::transaction(function () use ($cart, $orderCalculator) {
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
                $taxRate = (float) config('shop.tax_rate', 0.08);

                $totals = $orderCalculator->calculateMoney(
                    $this->buildCalculationItems($cart),
                    $taxRate,
                );

                // Create the order
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'order_number' => Order::generateOrderNumber(),
                    'subtotal' => $totals['subtotal']->getCents(),
                    'tax' => $totals['tax']->getCents(),
                    'total' => $totals['total']->getCents(),
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
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully!');
    }

    /**
     * Build line items payload for order calculations.
     *
     * @return array<int, array{product_id:int, price:int, quantity:int}>
     */
    private function buildCalculationItems(Cart $cart): array
    {
        return $cart->items->map(fn ($item) => [
            'product_id' => (int) $item->product_id,
            'price' => (int) $item->price_at_time,
            'quantity' => (int) $item->quantity,
        ])->values()->all();
    }
}
