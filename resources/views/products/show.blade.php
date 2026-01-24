<x-app-layout>
    <x-slot name="title">{{ $product->name }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="text-sm mb-6">
            <ol class="flex items-center space-x-2 text-gray-500">
                <li><a href="{{ route('products.index') }}" class="hover:text-white transition">Products</a></li>
                <li><span>/</span></li>
                <li><a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-white transition">{{ $product->category->name }}</a></li>
                <li><span>/</span></li>
                <li class="text-white">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Product Image -->
            <div class="bg-dark-800 border border-dark-700 rounded-lg overflow-hidden">
                <div class="aspect-square flex items-center justify-center bg-dark-700">
                    @if($product->image_path)
                        <img src="{{ Storage::url($product->image_path) }}" 
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <span class="text-9xl">📦</span>
                    @endif
                </div>
            </div>

            <!-- Product Info -->
            <div>
                <p class="text-purple-400 text-sm mb-2">{{ $product->category->name }}</p>
                <h1 class="text-3xl font-bold mb-4">{{ $product->name }}</h1>
                
                <div class="flex items-baseline space-x-4 mb-6">
                    <span class="text-4xl font-bold text-green-400">{{ formatMoney($product->price) }}</span>
                    @if($product->stock_quantity > 0)
                        <span class="text-sm text-gray-500">{{ $product->stock_quantity }} in stock</span>
                    @endif
                </div>

                <!-- Stock Status -->
                @if($product->stock_quantity == 0)
                    <div class="bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 rounded-lg mb-6">
                        <p class="font-medium">Out of Stock</p>
                        <p class="text-sm">This item is currently unavailable.</p>
                    </div>
                @elseif($product->stock_quantity <= 5)
                    <div class="bg-yellow-900/50 border border-yellow-700 text-yellow-300 px-4 py-3 rounded-lg mb-6">
                        <p class="font-medium">Low Stock Warning</p>
                        <p class="text-sm">Only {{ $product->stock_quantity }} left in stock - order soon!</p>
                    </div>
                @endif

                <!-- Add to Cart Form -->
                @if($product->stock_quantity > 0)
                    <form action="{{ route('cart.add', $product) }}" method="POST" class="mb-6">
                        @csrf
                        <div class="flex items-center space-x-4 mb-4">
                            <label for="quantity" class="text-sm text-gray-400">Quantity:</label>
                            <select name="quantity" id="quantity" 
                                    class="bg-dark-800 border border-dark-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                                @for($i = 1; $i <= min($product->stock_quantity, 10); $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" 
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 px-6 rounded-lg font-medium transition">
                            Add to Cart
                        </button>
                    </form>
                @endif

                <!-- Description -->
                <div class="border-t border-dark-700 pt-6">
                    <h2 class="font-semibold mb-3">Description</h2>
                    <div class="text-gray-400 prose prose-invert prose-sm max-w-none">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>

                <!-- Product Details -->
                <div class="border-t border-dark-700 pt-6 mt-6">
                    <h2 class="font-semibold mb-3">Product Details</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex">
                            <dt class="text-gray-500 w-32">SKU:</dt>
                            <dd class="text-gray-300">{{ $product->slug }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="text-gray-500 w-32">Category:</dt>
                            <dd class="text-gray-300">{{ $product->category->name }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="text-gray-500 w-32">Availability:</dt>
                            <dd class="{{ $product->stock_quantity > 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $product->stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->isNotEmpty())
            <section class="mt-16">
                <h2 class="text-2xl font-bold mb-6">Related Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $related)
                        <a href="{{ route('products.show', $related) }}" 
                           class="bg-dark-800 border border-dark-700 rounded-lg overflow-hidden hover:border-purple-500 transition group">
                            <div class="aspect-square bg-dark-700 flex items-center justify-center">
                                @if($related->image_path)
                                    <img src="{{ Storage::url($related->image_path) }}" 
                                         alt="{{ $related->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <span class="text-4xl">📦</span>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-medium text-white group-hover:text-purple-400 transition truncate">
                                    {{ $related->name }}
                                </h3>
                                <p class="text-lg font-bold text-green-400 mt-2">
                                    {{ formatMoney($related->price) }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
