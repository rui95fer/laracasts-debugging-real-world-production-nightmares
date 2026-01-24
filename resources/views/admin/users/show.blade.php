<x-admin-layout>
    <x-slot name="title">User: {{ $user->name }}</x-slot>

    <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-white text-sm transition mb-4 inline-block">
        ← Back to Users
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- User Info -->
        <div class="lg:col-span-1">
            <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-2xl font-medium">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ $user->name }}</h2>
                        @if($user->is_admin)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-900/50 text-purple-300">
                                Admin
                            </span>
                        @endif
                    </div>
                </div>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Email</dt>
                        <dd>{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Timezone</dt>
                        <dd>{{ $user->timezone }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Joined</dt>
                        <dd>{{ $user->created_at->format('F j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Total Orders</dt>
                        <dd>{{ $user->orders->count() }}</dd>
                    </div>
                </dl>

                @if($user->id !== auth()->id())
                    <div class="mt-6 pt-6 border-t border-dark-700">
                        <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2 rounded-lg text-sm transition
                                {{ $user->is_admin ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-purple-600 hover:bg-purple-700 text-white' }}">
                                {{ $user->is_admin ? 'Remove Admin Role' : 'Grant Admin Role' }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- User Orders -->
        <div class="lg:col-span-2">
            <div class="bg-dark-800 border border-dark-700 rounded-lg">
                <div class="p-4 border-b border-dark-700">
                    <h2 class="font-semibold">Order History</h2>
                </div>

                @if($user->orders->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        No orders yet
                    </div>
                @else
                    <div class="divide-y divide-dark-700">
                        @foreach($user->orders as $order)
                            <div class="p-4 flex items-center justify-between">
                                <div>
                                    <p class="font-mono text-sm">{{ $order->order_number }}</p>
                                    <p class="text-sm text-gray-500">{{ $order->items->count() }} items</p>
                                </div>
                                <div class="text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $order->status === 'completed' ? 'bg-green-900/50 text-green-300' : '' }}
                                        {{ $order->status === 'processing' ? 'bg-blue-900/50 text-blue-300' : '' }}
                                        {{ $order->status === 'pending' ? 'bg-yellow-900/50 text-yellow-300' : '' }}
                                        {{ $order->status === 'refunded' ? 'bg-red-900/50 text-red-300' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-green-400">{{ $order->formatted_total }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->placed_at->format('M j, Y') }}</p>
                                </div>
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="text-purple-400 hover:text-purple-300 text-sm">
                                    View →
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
