<x-app-layout>
    <x-slot name="title">Shopping Cart</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>

        @if(!$cart || $cart->items->isEmpty())
            <div class="bg-dark-800 border border-dark-700 rounded-lg p-12 text-center">
                <span class="text-6xl mb-4 block">🛒</span>
                <h3 class="text-lg font-medium mb-2">Your cart is empty</h3>
                <p class="text-gray-500 mb-6">Looks like you haven't added anything to your cart yet.</p>
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition">
                    Start Shopping
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($cart->items as $item)
                        <div class="bg-dark-800 border border-dark-700 rounded-lg p-4 flex items-center space-x-4">
                            <!-- Product Image -->
                            <div class="w-20 h-20 bg-dark-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                @if($item->product->image_path)
                                    <img src="{{ Storage::url($item->product->image_path) }}" 
                                         alt="{{ $item->product->name }}"
                                         class="w-full h-full object-cover rounded-lg">
                                @else
                                    <span class="text-2xl">📦</span>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('products.show', $item->product) }}" 
                                   class="font-medium hover:text-purple-400 transition truncate block">
                                    {{ $item->product->name }}
                                </a>
                                <p class="text-sm text-gray-500">{{ formatMoney($item->price_at_time) }} each</p>
                                
                                <!-- Quantity Update -->
                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="mt-2 flex items-center space-x-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="quantity" 
                                            onchange="this.form.submit()"
                                            class="bg-dark-700 border border-dark-600 rounded px-2 py-1 text-sm text-white">
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ $item->quantity == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </form>
                            </div>

                            <!-- Price & Remove -->
                            <div class="text-right">
                                <p class="font-bold text-green-400">{{ $item->formatted_subtotal }}</p>
                                <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-400 hover:text-red-300 transition">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <!-- Clear Cart -->
                    <div class="text-right">
                        <form action="{{ route('cart.clear') }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-gray-500 hover:text-red-400 transition">
                                Clear entire cart
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-dark-800 border border-dark-700 rounded-lg p-6 sticky top-24">
                        <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal ({{ $cart->items->sum('quantity') }} items)</span>
                                <span>{{ formatMoney($cartTotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tax (8%)</span>
                                <span>{{ formatMoney((int) round($cartTotal * 0.08)) }}</span>
                            </div>
                            <hr class="border-dark-600">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span class="text-green-400">{{ formatMoney($cartTotal + (int) round($cartTotal * 0.08)) }}</span>
                            </div>
                        </div>

                        <a href="{{ route('checkout.index') }}" 
                           class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-3 rounded-lg font-medium transition mt-6">
                            Proceed to Checkout
                        </a>

                        <a href="{{ route('products.index') }}" 
                           class="block text-center text-sm text-gray-500 hover:text-white transition mt-4">
                            Continue Shopping
                        </a>
                    </div>

                    <!-- Episode 5 Bug Notice -->
                    <div class="bg-yellow-900/30 border border-yellow-700/50 rounded-lg p-4 mt-4 text-sm">
                        <p class="text-yellow-300">
                            <strong>🐛 Episode 5:</strong> Cart totals are cached with a global key. 
                            Different users might see each other's totals!
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
