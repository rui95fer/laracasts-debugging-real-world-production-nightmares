<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Search for products
     * 
     * Episode 9 BUG: Multiple search performance nightmares
     * - Unbounded LIKE queries
     * - No pagination limits
     * - Eager loading explosion
     */
    public function index(Request $request)
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return view('search.index', [
                'products' => collect(),
                'query' => '',
            ]);
        }

        // Episode 8 BUG: Logging every search query
        Log::info("Search performed", [
            'query' => $query,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(), // BUG: Logging user agent
        ]);

        // ============================================
        // EPISODE 9 BUG: Performance Nightmare!
        // ============================================
        
        // BUG 1: LIKE with wildcards on BOTH sides - can't use index!
        // This does a full table scan on every search
        $products = Product::where('name', 'LIKE', "%{$query}%")
            // BUG 2: Also searching description with double wildcard
            ->orWhere('description', 'LIKE', "%{$query}%")
            // BUG 3: Eager loading explosion - loads ALL related data
            ->with('category')
            // BUG 4: No limit! Could return 10,000+ products
            ->get();

        // Episode 8 BUG: Logging in response
        Log::debug("Search results", [
            'query' => $query,
            'result_count' => $products->count(),
            'products' => $products->pluck('id')->toArray(), // BUG: Logging all IDs
        ]);

        return view('search.index', [
            'products' => $products, // BUG: Passing potentially massive collection
            'query' => $query,
        ]);
    }

    /**
     * Admin search for orders
     * 
     * Episode 9: Another example of problematic search
     */
    public function adminOrderSearch(Request $request)
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return response()->json(['orders' => []]);
        }

        // ============================================
        // EPISODE 9 BUG: Even worse for orders!
        // ============================================
        
        // BUG: Searching multiple text fields with LIKE
        $orders = \App\Models\Order::where('order_number', 'LIKE', "%{$query}%")
            ->orWhereHas('user', function ($q) use ($query) {
                // BUG: Subquery with LIKE
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            // BUG: Loading ALL relationships
            ->with(['user', 'items', 'items.product'])
            // BUG: No limit!
            ->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }
}
