<x-app-layout>
    <x-slot name="title">My Orders</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold mb-8">My Orders</h1>

        @if($orders->isEmpty())
            <div class="bg-dark-800 border border-dark-700 rounded-lg p-12 text-center">
                <span class="text-6xl mb-4 block">📋</span>
                <h3 class="text-lg font-medium mb-2">No orders yet</h3>
                <p class="text-gray-500 mb-6">You haven't placed any orders yet.</p>
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition">
                    Start Shopping
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="bg-dark-800 border border-dark-700 rounded-lg overflow-hidden">
                        <!-- Order Header -->
                        <div class="p-4 bg-dark-900 flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Order Number</p>
                                <p class="font-mono font-medium">{{ $order->order_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Placed</p>
                                {{-- Episode 7 BUG: Not converting to user timezone! --}}
                                <p>{{ $order->placed_at->format('M j, Y g:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total</p>
                                <p class="font-bold text-green-400">{{ $order->formatted_total }}</p>
                            </div>
                            <div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $order->status === 'completed' ? 'bg-green-900/50 text-green-300' : '' }}
                                    {{ $order->status === 'processing' ? 'bg-blue-900/50 text-blue-300' : '' }}
                                    {{ $order->status === 'pending' ? 'bg-yellow-900/50 text-yellow-300' : '' }}
                                    {{ $order->status === 'refunded' ? 'bg-red-900/50 text-red-300' : '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="p-4">
                            <div class="space-y-3">
                                @foreach($order->items->take(3) as $item)
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-dark-700 rounded flex items-center justify-center flex-shrink-0">
                                            <span class="text-lg">📦</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium truncate">{{ $item->product->name }}</p>
                                            <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                        </div>
                                        <p class="text-sm">{{ $item->formatted_subtotal }}</p>
                                    </div>
                                @endforeach
                                @if($order->items->count() > 3)
                                    <p class="text-sm text-gray-500">+ {{ $order->items->count() - 3 }} more items</p>
                                @endif
                            </div>

                            <div class="mt-4 flex items-center justify-between pt-4 border-t border-dark-700">
                                <a href="{{ route('orders.show', $order) }}" 
                                   class="text-purple-400 hover:text-purple-300 text-sm transition">
                                    View Details →
                                </a>
                                
                                {{-- Episode 3 BUG: Refund button visible to all users! --}}
                                @if($order->canBeRefunded())
                                    <form action="{{ route('orders.refund', $order) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to refund this order?')">
                                        @csrf
                                        <button type="submit" 
                                                class="text-red-400 hover:text-red-300 text-sm transition">
                                            Request Refund
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $orders->links() }}
            </div>

            <!-- Episode 3 Bug Notice -->
            <div class="bg-yellow-900/30 border border-yellow-700/50 rounded-lg p-4 mt-6 text-sm">
                <p class="text-yellow-300">
                    <strong>🐛 Episode 3:</strong> The refund button is visible to all users, and the 
                    route doesn't check if you're an admin!
                </p>
            </div>
        @endif
    </div>
</x-app-layout>
