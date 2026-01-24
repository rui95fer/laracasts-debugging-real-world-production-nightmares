<x-app-layout>
    <x-slot name="title">Checkout</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold mb-8">Checkout</h1>

        <!-- Bug Notices -->
        <div class="bg-yellow-900/30 border border-yellow-700/50 rounded-lg p-4 mb-6 text-sm">
            <p class="text-yellow-300">
                <strong>🐛 Episode 1:</strong> Tax rate uses env() directly - breaks with config:cache!
            </p>
            <p class="text-yellow-300 mt-1">
                <strong>🐛 Episode 4:</strong> Inventory check is non-atomic - race conditions possible!
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Review -->
            <div class="lg:col-span-2">
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">Order Review</h2>
                    
                    <div class="space-y-4">
                        @foreach($cart->items as $item)
                            <div class="flex items-center space-x-4 py-3 border-b border-dark-700 last:border-0">
                                <div class="w-16 h-16 bg-dark-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                    @if($item->product->image_path)
                                        <img src="{{ Storage::url($item->product->image_path) }}" 
                                             alt="{{ $item->product->name }}"
                                             class="w-full h-full object-cover rounded-lg">
                                    @else
                                        <span class="text-xl">📦</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ $item->product->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ formatMoney($item->price_at_time) }} × {{ $item->quantity }}
                                    </p>
                                    @if($item->product->stock_quantity <= $item->quantity)
                                        <p class="text-xs text-yellow-400 mt-1">
                                            ⚠️ Low stock - only {{ $item->product->stock_quantity }} available
                                        </p>
                                    @endif
                                </div>
                                <p class="font-bold text-green-400">{{ $item->formatted_subtotal }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Shipping Info -->
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4">Shipping Information</h2>
                    <p class="text-gray-500 text-sm">
                        For this demo, orders ship to the address associated with your account.
                    </p>
                    <div class="mt-4 p-4 bg-dark-700 rounded-lg">
                        <p class="font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-sm text-gray-400">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Summary & Payment -->
            <div class="lg:col-span-1">
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-6 sticky top-24">
                    <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Subtotal</span>
                            <span>{{ formatMoney($subtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tax ({{ $taxRate * 100 }}%)</span>
                            <span>{{ formatMoney($tax) }}</span>
                        </div>
                        <hr class="border-dark-600">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span class="text-green-400">{{ formatMoney($total) }}</span>
                        </div>
                    </div>

                    <!-- Debug Info -->
                    {{-- @if(config('app.debug'))
                        <div class="mt-4 p-3 bg-dark-700 rounded text-xs font-mono">
                            <p class="text-gray-500">Debug Info:</p>
                            <p class="text-yellow-400">env('TAX_RATE'): {{ var_export(env('TAX_RATE'), true) }}</p>
                            <p class="text-green-400">config('shop.tax_rate'): {{ config('shop.tax_rate') }}</p>
                            <p class="text-purple-400">Used rate: {{ $taxRate }}</p>
                        </div>
                    @endif --}}

                    <form action="{{ route('checkout.store') }}" method="POST" class="mt-6">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-medium transition">
                            Place Order
                        </button>
                    </form>

                    <a href="{{ route('cart.index') }}" 
                       class="block text-center text-sm text-gray-500 hover:text-white transition mt-4">
                        ← Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
