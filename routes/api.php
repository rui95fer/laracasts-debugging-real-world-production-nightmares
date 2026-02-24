<?php

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:120,1')->group(function () {
    Route::post('/test-checkout', function (Request $request) {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $request->validate([
            'user_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $user = User::find($request->integer('user_id'));
        $product = Product::find($request->integer('product_id'));
        $quantity = $request->integer('quantity');

        if (! $user || ! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user or product',
            ], 422);
        }

        if ($product->stock_quantity < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Out of stock',
            ]);
        }

        usleep(random_int(10000, 50000));

        $taxRate = config('shop.tax_rate', 0.08);
        $subtotal = $product->price * $quantity;
        $tax = (int) round($subtotal * $taxRate);
        $total = $subtotal + $tax;

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => Order::STATUS_PENDING,
            'placed_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'subtotal' => $subtotal,
        ]);

        $product->stock_quantity -= $quantity;
        $product->save();

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
        ]);
    });

    Route::post('/test-checkout-fixed', function (Request $request) {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $request->validate([
            'user_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $user = User::find($request->integer('user_id'));
        $productId = $request->integer('product_id');
        $quantity = $request->integer('quantity');

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user',
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($user, $productId, $quantity) {
                $product = Product::where('id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    throw new InsufficientStockException('Unknown product', $quantity, 0);
                }

                if ($product->stock_quantity < $quantity) {
                    throw new InsufficientStockException(
                        $product->name,
                        $quantity,
                        $product->stock_quantity
                    );
                }

                usleep(random_int(10000, 50000));

                $taxRate = config('shop.tax_rate', 0.08);
                $subtotal = $product->price * $quantity;
                $tax = (int) round($subtotal * $taxRate);

                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => Order::generateOrderNumber(),
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $subtotal + $tax,
                    'status' => Order::STATUS_PENDING,
                    'placed_at' => now(),
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock_quantity', $quantity);

                return $order;
            }, 3);

            return response()->json([
                'success' => true,
                'order_number' => $order->order_number,
            ]);
        } catch (InsufficientStockException) {
            return response()->json([
                'success' => false,
                'message' => 'Out of stock',
            ]);
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'cannot obtain lock on record',
            ], 500);
        }
    });
});
