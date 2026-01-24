<x-app-layout>
    <x-slot name="title">Welcome</x-slot>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-purple-900/50 to-dark-900 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Welcome to <span class="text-purple-400">NightmareMart</span>
            </h1>
            <p class="text-xl text-gray-400 mb-8 max-w-2xl mx-auto">
                Your one-stop shop for debugging nightmares. 
                Warning: This application contains intentional bugs for educational purposes! 🐛
            </p>
            <div class="flex justify-center space-x-4">
                <a href="{{ route('products.index') }}" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-medium transition">
                    Browse Products
                </a>
                <a href="{{ route('search') }}" 
                   class="bg-dark-700 hover:bg-dark-600 text-white px-8 py-3 rounded-lg font-medium transition border border-dark-500">
                    Search
                </a>
            </div>
        </div>
    </section>

    <!-- Bug Warning Banner -->
    <section class="bg-yellow-900/30 border-y border-yellow-700/50 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-center space-x-2 text-yellow-300">
                <span class="text-2xl">⚠️</span>
                <p class="text-sm">
                    <strong>Educational Project:</strong> This app contains 10 intentional production bugs for debugging practice.
                </p>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8">Featured Products</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
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
                            <h3 class="font-medium text-white group-hover:text-purple-400 transition truncate">
                                {{ $product->name }}
                            </h3>
                            <p class="text-lg font-bold text-green-400 mt-2">
                                {{ formatMoney($product->price) }}
                            </p>
                            @if($product->stock_quantity <= 5 && $product->stock_quantity > 0)
                                <p class="text-xs text-yellow-400 mt-1">Only {{ $product->stock_quantity }} left!</p>
                            @elseif($product->stock_quantity == 0)
                                <p class="text-xs text-red-400 mt-1">Out of stock</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center text-purple-400 hover:text-purple-300 transition">
                    View all products
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="py-16 bg-dark-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8">Shop by Category</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                       class="bg-dark-800 border border-dark-700 rounded-lg p-6 text-center hover:border-purple-500 transition">
                        <h3 class="font-medium text-white mb-1">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $category->products_count }} products</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Episode List -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8">🐛 Bugs Included</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4">
                    <span class="text-purple-400 font-mono text-sm">Episode 1</span>
                    <h3 class="font-medium mt-1">Config Caching Bug</h3>
                    <p class="text-sm text-gray-400 mt-1">env() vs config() in production</p>
                </div>
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4">
                    <span class="text-purple-400 font-mono text-sm">Episode 2</span>
                    <h3 class="font-medium mt-1">N+1 Query Problem</h3>
                    <p class="text-sm text-gray-400 mt-1">Hidden in admin order listing</p>
                </div>
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4">
                    <span class="text-purple-400 font-mono text-sm">Episode 3</span>
                    <h3 class="font-medium mt-1">Authorization Hole</h3>
                    <p class="text-sm text-gray-400 mt-1">Missing middleware on refund route</p>
                </div>
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4">
                    <span class="text-purple-400 font-mono text-sm">Episode 4</span>
                    <h3 class="font-medium mt-1">Race Condition</h3>
                    <p class="text-sm text-gray-400 mt-1">Inventory overselling</p>
                </div>
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4">
                    <span class="text-purple-400 font-mono text-sm">Episode 5</span>
                    <h3 class="font-medium mt-1">Cache Key Collision</h3>
                    <p class="text-sm text-gray-400 mt-1">Seeing other users' cart data</p>
                </div>
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4">
                    <span class="text-purple-400 font-mono text-sm">Episode 6</span>
                    <h3 class="font-medium mt-1">Memory Leak</h3>
                    <p class="text-sm text-gray-400 mt-1">Queue worker crashes</p>
                </div>
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4">
                    <span class="text-purple-400 font-mono text-sm">Episode 7</span>
                    <h3 class="font-medium mt-1">Timezone Nightmare</h3>
                    <p class="text-sm text-gray-400 mt-1">Wrong dates for international users</p>
                </div>
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4">
                    <span class="text-purple-400 font-mono text-sm">Episode 8</span>
                    <h3 class="font-medium mt-1">Log File Explosion</h3>
                    <p class="text-sm text-gray-400 mt-1">Disk full from logging</p>
                </div>
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4">
                    <span class="text-purple-400 font-mono text-sm">Episode 9</span>
                    <h3 class="font-medium mt-1">Search Performance</h3>
                    <p class="text-sm text-gray-400 mt-1">One search takes down the site</p>
                </div>
                <div class="bg-dark-800 border border-dark-700 rounded-lg p-4">
                    <span class="text-purple-400 font-mono text-sm">Episode 10</span>
                    <h3 class="font-medium mt-1">Silent Money Bug</h3>
                    <p class="text-sm text-gray-400 mt-1">Floating-point math errors</p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
