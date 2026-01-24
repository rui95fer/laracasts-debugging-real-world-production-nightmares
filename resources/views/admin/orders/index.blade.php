<x-admin-layout>
    <x-slot name="title">Orders</x-slot>

    <!-- Bug Notice -->
    <div class="bg-yellow-900/30 border border-yellow-700/50 rounded-lg p-4 mb-6">
        <h3 class="font-medium text-yellow-300 mb-2">🐛 Episode 2: N+1 Query Problem</h3>
        <p class="text-sm text-yellow-300/80">
            This page loads orders WITHOUT eager loading. Each row triggers additional queries for user, items, and products.
            With {{ $orders->total() }} orders, this generates thousands of queries!
        </p>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">All Orders</h2>
            <p class="text-sm text-gray-500">{{ $orders->total() }} total orders</p>
        </div>
        <form action="{{ route('admin.orders.export') }}" method="POST">
            @csrf
            <button type="submit" 
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">
                Export CSV
            </button>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-dark-800 border border-dark-700 rounded-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-dark-900">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Order</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Customer</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Items</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Total</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Status</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Date</th>
                    <th class="text-right px-4 py-3 text-sm font-medium text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700">
                @foreach($orders as $order)
                    <tr class="hover:bg-dark-700/50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm">{{ $order->order_number }}</span>
                        </td>
                        <td class="px-4 py-3">
                            {{-- BUG: N+1 - accessing user relationship without eager loading --}}
                            <p class="font-medium">{{ $order->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $order->user->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            {{-- BUG: N+1 - accessing items relationship --}}
                            <div class="text-sm">
                                @foreach($order->items->take(2) as $item)
                                    {{-- BUG: N+1 - accessing product through item --}}
                                    <p class="text-gray-400 truncate max-w-[150px]">{{ $item->product->name }}</p>
                                @endforeach
                                @if($order->items->count() > 2)
                                    <p class="text-gray-500">+{{ $order->items->count() - 2 }} more</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-green-400">{{ $order->formatted_total }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $order->status === 'completed' ? 'bg-green-900/50 text-green-300' : '' }}
                                {{ $order->status === 'processing' ? 'bg-blue-900/50 text-blue-300' : '' }}
                                {{ $order->status === 'pending' ? 'bg-yellow-900/50 text-yellow-300' : '' }}
                                {{ $order->status === 'refunded' ? 'bg-red-900/50 text-red-300' : '' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-400">
                            {{-- Episode 7 BUG: UTC time, not user timezone --}}
                            {{ $order->placed_at->format('M j, Y') }}
                            <br>
                            <span class="text-xs">{{ $order->placed_at->format('g:i A') }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" 
                               class="text-purple-400 hover:text-purple-300 text-sm">
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $orders->links() }}
    </div>

    <!-- Debug Info -->
    @if(config('app.debug'))
        <div class="mt-6 bg-dark-800 border border-dark-700 rounded-lg p-4">
            <h3 class="font-medium text-gray-400 mb-2">Query Debug</h3>
            <p class="text-sm text-gray-500">
                Check Laravel Debugbar to see how many queries this page executes!
                With {{ $orders->count() }} orders displayed, you'll see N+1 queries for:
            </p>
            <ul class="text-sm text-gray-500 mt-2 list-disc list-inside">
                <li>1 query for orders</li>
                <li>{{ $orders->count() }} queries for users (N+1)</li>
                <li>{{ $orders->count() }} queries for items (N+1)</li>
                <li>Multiple queries for products through items (N+1)</li>
            </ul>
        </div>
    @endif
</x-admin-layout>
