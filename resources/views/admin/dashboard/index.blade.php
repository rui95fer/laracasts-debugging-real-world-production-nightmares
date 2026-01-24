<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Orders -->
        <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Today's Orders</p>
                    <p class="text-3xl font-bold mt-1">{{ $stats['today_orders'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-600/20 rounded-lg flex items-center justify-center">
                    <span class="text-2xl">📦</span>
                </div>
            </div>
            <p class="text-sm text-green-400 mt-2">
                {{ formatMoney($stats['today_revenue']) }} revenue
            </p>
        </div>

        <!-- Monthly Orders -->
        <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">This Month</p>
                    <p class="text-3xl font-bold mt-1">{{ $stats['month_orders'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center">
                    <span class="text-2xl">📊</span>
                </div>
            </div>
            <p class="text-sm text-green-400 mt-2">
                {{ formatMoney($stats['month_revenue']) }} revenue
            </p>
        </div>

        <!-- Pending Orders -->
        <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending Orders</p>
                    <p class="text-3xl font-bold mt-1">{{ $stats['pending_orders'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-600/20 rounded-lg flex items-center justify-center">
                    <span class="text-2xl">⏳</span>
                </div>
            </div>
            <p class="text-sm text-yellow-400 mt-2">
                {{ $stats['processing_orders'] }} processing
            </p>
        </div>

        <!-- Low Stock -->
        <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Low Stock Items</p>
                    <p class="text-3xl font-bold mt-1">{{ $stats['low_stock_products'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-600/20 rounded-lg flex items-center justify-center">
                    <span class="text-2xl">⚠️</span>
                </div>
            </div>
            <p class="text-sm text-red-400 mt-2">
                {{ $stats['out_of_stock'] }} out of stock
            </p>
        </div>
    </div>

    <!-- Bug Notices -->
    <div class="bg-yellow-900/30 border border-yellow-700/50 rounded-lg p-4 mb-8">
        <h3 class="font-medium text-yellow-300 mb-2">🐛 Bugs Active in This View</h3>
        <ul class="text-sm text-yellow-300/80 space-y-1">
            <li><strong>Episode 7:</strong> Date filtering uses server timezone, not user timezone</li>
            <li><strong>Episode 10:</strong> Revenue calculations may have floating-point issues</li>
        </ul>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Orders -->
        <div class="bg-dark-800 border border-dark-700 rounded-lg">
            <div class="p-4 border-b border-dark-700 flex items-center justify-between">
                <h2 class="font-semibold">Recent Orders</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm text-purple-400 hover:text-purple-300">View all →</a>
            </div>
            <div class="divide-y divide-dark-700">
                @foreach($recentOrders as $order)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-mono text-sm">{{ $order->order_number }}</p>
                            <p class="text-sm text-gray-500">{{ $order->user->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-400">{{ $order->formatted_total }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $order->status === 'completed' ? 'bg-green-900/50 text-green-300' : '' }}
                                {{ $order->status === 'processing' ? 'bg-blue-900/50 text-blue-300' : '' }}
                                {{ $order->status === 'pending' ? 'bg-yellow-900/50 text-yellow-300' : '' }}
                                {{ $order->status === 'refunded' ? 'bg-red-900/50 text-red-300' : '' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="bg-dark-800 border border-dark-700 rounded-lg">
            <div class="p-4 border-b border-dark-700 flex items-center justify-between">
                <h2 class="font-semibold">Low Stock Products</h2>
                <a href="{{ route('admin.products.index') }}" class="text-sm text-purple-400 hover:text-purple-300">View all →</a>
            </div>
            <div class="divide-y divide-dark-700">
                @foreach($lowStockProducts as $product)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium">{{ $product->name }}</p>
                            <p class="text-sm text-gray-500">{{ formatMoney($product->price) }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $product->stock_quantity === 0 ? 'bg-red-900/50 text-red-300' : 'bg-yellow-900/50 text-yellow-300' }}">
                                {{ $product->stock_quantity }} in stock
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="mt-8 bg-dark-800 border border-dark-700 rounded-lg p-6">
        <h2 class="font-semibold mb-4">System Overview</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold">{{ $stats['total_orders'] }}</p>
                <p class="text-sm text-gray-500">Total Orders</p>
            </div>
            <div>
                <p class="text-2xl font-bold">{{ $stats['total_products'] }}</p>
                <p class="text-sm text-gray-500">Products</p>
            </div>
            <div>
                <p class="text-2xl font-bold">{{ $stats['total_users'] }}</p>
                <p class="text-sm text-gray-500">Users</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-400">{{ formatMoney($stats['total_revenue']) }}</p>
                <p class="text-sm text-gray-500">Total Revenue</p>
            </div>
        </div>
    </div>
</x-admin-layout>
