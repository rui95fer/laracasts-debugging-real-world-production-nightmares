<x-app-layout>
    <x-slot name="title">Search Results</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Search Form -->
        <div class="mb-8">
            <form action="{{ route('search') }}" method="GET" class="max-w-2xl">
                <div class="relative">
                    <input type="text" 
                           name="q" 
                           value="{{ $query }}"
                           placeholder="Search products..."
                           class="w-full bg-dark-800 border border-dark-600 rounded-lg py-3 pl-12 pr-4 text-lg text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button type="submit" class="absolute inset-y-0 right-0 pr-4 flex items-center text-purple-400 hover:text-purple-300">
                        Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Bug Notice -->
        <div class="bg-yellow-900/30 border border-yellow-700/50 rounded-lg p-4 mb-6 text-sm">
            <p class="text-yellow-300">
                <strong>🐛 Episode 9:</strong> Search uses LIKE %query% on large dataset without pagination or limits!
                Try searching for common terms like "widget" or "premium" to see performance issues.
            </p>
        </div>

        @if($query)
            <h1 class="text-2xl font-bold mb-2">
                Search results for "{{ $query }}"
            </h1>
            <p class="text-gray-500 mb-8">{{ $products->count() }} products found</p>

            @if($products->isEmpty())
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-12 text-center">
                    <span class="text-6xl mb-4 block">🔍</span>
                    <h3 class="text-lg font-medium mb-2">No products found</h3>
                    <p class="text-gray-500">Try searching for something else.</p>
                </div>
            @else
                {{-- BUG: No pagination, all results rendered at once! --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        <a href="{{ route('products.show', $product) }}" 
                           class="bg-dark-800 border border-dark-700 rounded-lg overflow-hidden hover:border-purple-500 transition group">
                            <div class="aspect-square bg-dark-700 flex items-center justify-center">
                                @if($product->image_path)
                                    <img src="{{ Storage::url($product->image_path) }}" 
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <span class="text-4xl">📦</span>
                                @endif
                            </div>
                            <div class="p-4">
                                <p class="text-xs text-purple-400 mb-1">{{ $product->category->name }}</p>
                                <h3 class="font-medium text-white group-hover:text-purple-400 transition truncate">
                                    {{ $product->name }}
                                </h3>
                                <p class="text-lg font-bold text-green-400 mt-2">
                                    {{ formatMoney($product->price) }}
                                </p>
                                @if($product->stock_quantity == 0)
                                    <p class="text-xs text-red-400 mt-1">Out of stock</p>
                                @elseif($product->stock_quantity <= 5)
                                    <p class="text-xs text-yellow-400 mt-1">Only {{ $product->stock_quantity }} left!</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Note: No pagination because of bug --}}
                @if($products->count() >= 100)
                    <div class="bg-red-900/30 border border-red-700/50 rounded-lg p-4 mt-6 text-sm">
                        <p class="text-red-300">
                            <strong>⚠️ Performance Warning:</strong> Search returned {{ $products->count() }} results 
                            without pagination. In production, this would be very slow!
                        </p>
                    </div>
                @endif
            @endif
        @else
            <div class="bg-dark-800 border border-dark-700 rounded-lg p-12 text-center">
                <span class="text-6xl mb-4 block">🔍</span>
                <h3 class="text-lg font-medium mb-2">Search for products</h3>
                <p class="text-gray-500">Enter a search term above to find products.</p>
            </div>
        @endif
    </div>
</x-app-layout>
