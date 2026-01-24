<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display all products
     */
    public function index()
    {
        $products = Product::with('category')
            ->withTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.products.index', [
            'products' => $products,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = Category::all();

        return view('admin.products.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store new product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        // Convert price from dollars to cents
        $validated['price'] = (int) round($validated['price'] * 100);
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')
                ->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Product $product)
    {
        $categories = Category::all();

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        // Convert price from dollars to cents
        $validated['price'] = (int) round($validated['price'] * 100);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')
                ->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    /**
     * Restore soft-deleted product
     */
    public function restore(int $id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product restored.');
    }
}
