<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $query = trim((string) $request->input('q', ''));

        if (empty($query)) {
            return view('search.index', [
                'products' => collect(),
                'query' => '',
            ]);
        }

        // Episode 8 BUG: Logging every search query
        Log::info("Search performed", [
            'query' => $query,
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(), // BUG: Logging user agent
        ]);

        // ============================================
        // EPISODE 9 BUG: Performance Nightmare!
        // ============================================
        
        $minLength = max(1, (int) config('shop.search.min_length', 3));
        $maxPerPage = max(1, (int) config('shop.search.max_results', 100));
        $defaultPerPage = max(1, (int) config('shop.items_per_page', 20));
        $perPage = max(1, min($request->integer('per_page', $defaultPerPage), $maxPerPage));

        if (mb_strlen($query) < $minLength) {
            return view('search.index', [
                'products' => collect(),
                'query' => $query,
            ]);
        }

        $productsQuery = Product::query()
            ->with('category')
            ->select(['id', 'category_id', 'name', 'slug', 'price', 'stock_quantity', 'image_path']);

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            $productsQuery->whereFullText(['name', 'description'], $query);
        } else {
            $productsQuery->where('name', 'LIKE', $query . '%')
                ->orWhere('description', 'LIKE', '%' . $query . '%');
        }

        $products = $productsQuery
            ->latest('id')
            ->simplePaginate($perPage)
            ->withQueryString();

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
