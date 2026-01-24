<x-app-layout>
    <x-slot name="title">Products</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar - Categories -->
            <aside class="w-full lg:w-64 flex-shrink-0">
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4 sticky top-24">
                    <h3 class="font-semibold mb-4">Categories</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('products.index') }}" 
                               class="block px-3 py-2 rounded-md transition
                                      {{ !$selectedCategory ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-dark-700' }}">
                                All Products
                            </a>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                                   class="block px-3 py-2 rounded-md transition
                                          {{ $selectedCategory === $category->slug ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-dark-700' }}">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            <!-- Products Grid -->
            <div class="flex-1">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold">
                        @if($selectedCategory)
                            {{ $categories->firstWhere('slug', $selectedCategory)->name ?? 'Products' }}
                        @else
                            All Products
                        @endif
                    </h1>
                    <p class="text-gray-500">{{ $products->total() }} products</p>
                </div>

                @if($products->isEmpty())
                    <div class="bg-dark-800 border border-dark-700 rounded-lg p-12 text-center">
                        <span class="text-6xl mb-4 block">📦</span>
                        <h3 class="text-lg font-medium mb-2">No products found</h3>
                        <p class="text-gray-500">Try selecting a different category or check back later.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <a href="{{ route('products.show', $product) }}" 
                               class="bg-dark-800 border border-dark-700 rounded-lg overflow-hidden hover:border-purple-500 transition group">
                                <div class="aspect-square bg-dark-700 flex items-center justify-center">
                                    @if($product->image_path)
                                        <img src="{{ Storage::url($product->image_path) }}" 
                                             alt="{{ $product->name }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <span class="text-6xl">📦</span>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <p class="text-xs text-purple-400 mb-1">{{ $product->category->name }}</p>
                                    <h3 class="font-medium text-white group-hover:text-purple-400 transition">
                                        {{ $product->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">
                                        {{ Str::limit($product->description, 80) }}
                                    </p>
                                    <div class="flex items-center justify-between mt-3">
                                        <p class="text-lg font-bold text-green-400">
                                            {{ formatMoney($product->price) }}
                                        </p>
                                        @if($product->stock_quantity <= 5 && $product->stock_quantity > 0)
                                            <span class="text-xs text-yellow-400">Only {{ $product->stock_quantity }} left!</span>
                                        @elseif($product->stock_quantity == 0)
                                            <span class="text-xs text-red-400">Out of stock</span>
                                        @else
                                            <span class="text-xs text-green-400">In stock</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
