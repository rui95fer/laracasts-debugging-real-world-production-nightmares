<x-admin-layout>
    <x-slot name="title">Add Product</x-slot>

    <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:text-white text-sm transition mb-4 inline-block">
        ← Back to Products
    </a>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold mb-6">Add New Product</h1>

        <div class="bg-dark-800 border border-dark-700 rounded-lg p-6">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Product Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                    <select name="category_id" id="category_id" required
                            class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea name="description" id="description" rows="4" required
                              class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-300 mb-2">Price ($)</label>
                        <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01" min="0" required
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 @error('price') border-red-500 @enderror">
                        @error('price')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="stock_quantity" class="block text-sm font-medium text-gray-300 mb-2">Stock Quantity</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" required
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 @error('stock_quantity') border-red-500 @enderror">
                        @error('stock_quantity')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-300 mb-2">Product Image</label>
                    <input type="file" name="image" id="image" accept="image/*"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @error('image')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.products.index') }}" 
                       class="px-6 py-2 border border-dark-600 rounded-lg text-gray-400 hover:text-white transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                        Create Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
