<x-admin-layout>
    <x-slot name="title">Order {{ $order->order_number }}</x-slot>

    <!-- Back Link -->
    <a href="{{ route('admin.orders.index') }}" class="text-gray-500 hover:text-white text-sm transition mb-4 inline-block">
        ← Back to Orders
    </a>

    <div class="flex items-start justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold">Order Details</h1>
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
                    {{-- Episode 2: Without eager loading, each item access triggers queries --}}
                    @foreach($order->items as $item)
                        <div class="p-4 flex items-center space-x-4">
                            <div class="w-16 h-16 bg-dark-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-2xl">📦</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                {{-- N+1 query here --}}
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

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Order Summary -->
            <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
                <h2 class="font-semibold mb-4">Summary</h2>
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

            <!-- Customer Info -->
            <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
                <h2 class="font-semibold mb-4">Customer</h2>
                <div class="space-y-2 text-sm">
                    {{-- Episode 2: N+1 if not eager loaded --}}
                    <p class="font-medium">{{ $order->user->name }}</p>
                    <p class="text-gray-500">{{ $order->user->email }}</p>
                    <p class="text-gray-500">Timezone: {{ $order->user->timezone }}</p>
                </div>
            </div>

            <!-- Update Status -->
            <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
                <h2 class="font-semibold mb-4">Update Status</h2>
                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <select name="status" 
                            class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2 text-white mb-3">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="refunded" {{ $order->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                    <button type="submit" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg text-sm transition">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- Order Meta -->
            <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
                <h2 class="font-semibold mb-4">Details</h2>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-gray-500">Order ID</dt>
                        <dd>{{ $order->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Placed At (UTC)</dt>
                        {{-- Episode 7: Shows UTC --}}
                        <dd>{{ $order->placed_at->format('F j, Y g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Created</dt>
                        <dd>{{ $order->created_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-admin-layout>
