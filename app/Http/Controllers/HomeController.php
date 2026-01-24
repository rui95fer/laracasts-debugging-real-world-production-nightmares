<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display home page with featured products
     */
    public function index()
    {
        // Featured products (latest)
        $featuredProducts = Product::with('category')
            ->where('stock_quantity', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Categories for navigation
        $categories = Category::withCount('products')->get();

        return view('home', [
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
        ]);
    }
}
