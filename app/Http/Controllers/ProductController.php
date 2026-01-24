<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display product listing
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Episode 2: Eager load to avoid N+1 (this is done correctly here)
        $products = $query->with('category')
            ->paginate(config('shop.items_per_page', 20));

        $categories = Category::all();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $request->category,
        ]);
    }

    /**
     * Display single product
     */
    public function show(Product $product)
    {
        // Load category relationship
        $product->load('category');

        // Get related products from same category
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
