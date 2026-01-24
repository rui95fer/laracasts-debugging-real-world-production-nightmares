<x-admin-layout>
    <x-slot name="title">Products</x-slot>

    <!-- Actions -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">All Products</h2>
            <p class="text-sm text-gray-500">{{ $products->total() }} products</p>
        </div>
        <a href="{{ route('admin.products.create') }}" 
           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">
            Add Product
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-dark-800 border border-dark-700 rounded-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-dark-900">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Product</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Category</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Price</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Stock</th>
                    <th class="text-left px-4 py-3 text-sm font-medium text-gray-400">Status</th>
                    <th class="text-right px-4 py-3 text-sm font-medium text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700">
                @foreach($products as $product)
                    <tr class="hover:bg-dark-700/50 transition {{ $product->trashed() ? 'opacity-50' : '' }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-dark-700 rounded flex items-center justify-center">
                                    <span class="text-lg">📦</span>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $product->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-400">
                            {{ $product->category->name }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-green-400">{{ formatMoney($product->price) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="{{ $product->stock_quantity <= 5 ? 'text-yellow-400' : '' }} {{ $product->stock_quantity == 0 ? 'text-red-400' : '' }}">
                                {{ $product->stock_quantity }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($product->trashed())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-900/50 text-red-300">
                                    Deleted
                                </span>
                            @elseif($product->stock_quantity == 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-900/50 text-red-300">
                                    Out of Stock
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-900/50 text-green-300">
                                    Active
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            @if($product->trashed())
                                <form action="{{ route('admin.products.restore', $product->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-400 hover:text-green-300 text-sm">
                                        Restore
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="text-purple-400 hover:text-purple-300 text-sm">
                                    Edit
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->links() }}
    </div>
</x-admin-layout>
