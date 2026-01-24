<x-app-layout>
    <x-slot name="title">Order {{ $order->order_number }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Link -->
        <a href="{{ route('orders.index') }}" class="text-gray-500 hover:text-white text-sm transition mb-4 inline-block">
            ← Back to Orders
        </a>

        <div class="flex items-start justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Order Details</h1>
                <p class="text-gray-500 font-mono mt-1">{{ $order->order_number }}</p>
            </div>
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                {{ $order->status === 'completed' ? 'bg-green-900/50 text-green-300' : '' }}
                {{ $order->status === 'processing' ? 'bg-blue-900/50 text-blue-300' : '' }}
                {{ $order->status === 'pending' ? 'bg-yellow-900/50 text-yellow-300' : '' }}
                {{ $order->status === 'refunded' ? 'bg-red-900/50 text-red-300' : '' }}">
                {{ ucfirst($order->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Items -->
            <div class="lg:col-span-2">
                <div class="bg-dark-800 border border-dark-700 rounded-lg">
                    <div class="p-4 border-b border-dark-700">
                        <h2 class="font-semibold">Items ({{ $order->items->count() }})</h2>
                    </div>
                    <div class="divide-y divide-dark-700">
                        @foreach($order->items as $item)
                            <div class="p-4 flex items-center space-x-4">
                                <div class="w-16 h-16 bg-dark-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                    @if($item->product->image_path)
                                        <img src="{{ Storage::url($item->product->image_path) }}" 
                                             alt="{{ $item->product->name }}"
                                             class="w-full h-full object-cover rounded-lg">
                                    @else
                                        <span class="text-2xl">📦</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium">{{ $item->product->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ formatMoney($item->price) }} × {{ $item->quantity }}
                                    </p>
                                </div>
                                <p class="font-bold">{{ $item->formatted_subtotal }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Totals -->
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Order Summary</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Subtotal</span>
                            <span>{{ $order->formatted_subtotal }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tax</span>
                            <span>{{ $order->formatted_tax }}</span>
                        </div>
                        <hr class="border-dark-600">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span class="text-green-400">{{ $order->formatted_total }}</span>
                        </div>
                    </div>
                </div>

                <!-- Order Info -->
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Order Information</h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Order Date</dt>
                            {{-- Episode 7 BUG: Shows UTC, not user timezone --}}
                            <dd>{{ $order->placed_at->format('F j, Y \a\t g:i A') }}</dd>
                            <dd class="text-xs text-gray-600">(UTC)</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Customer</dt>
                            <dd>{{ $order->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd>{{ $order->user->email }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Actions</h2>
                    <div class="space-y-3">
                        <form action="{{ route('orders.receipt', $order) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-dark-700 hover:bg-dark-600 text-white py-2 px-4 rounded-lg text-sm transition">
                                Download Receipt
                            </button>
                        </form>

                        @if($order->canBeRefunded())
                            <form action="{{ route('orders.refund', $order) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to refund this order?')">
                                @csrf
                                <button type="submit" 
                                        class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg text-sm transition">
                                    Request Refund
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Bug Notice -->
                <div class="bg-yellow-900/30 border border-yellow-700/50 rounded-lg p-4 text-sm">
                    <p class="text-yellow-300">
                        <strong>🐛 Episode 7:</strong> Dates shown in UTC, not your timezone ({{ auth()->user()->timezone }})!
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
