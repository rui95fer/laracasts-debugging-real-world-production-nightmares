<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     * 
     * Episode 7 BUG: Timezone issues in statistics
     * Episode 10 BUG: Money calculations
     */
    public function index()
    {
        // ============================================
        // EPISODE 7 BUG: Timezone-naive calculations
        // These dates use server timezone, not user timezone
        // ============================================
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // Order statistics
        $stats = [
            'total_orders' => Order::count(),
            'today_orders' => Order::where('placed_at', '>=', $today)->count(),
            'week_orders' => Order::where('placed_at', '>=', $thisWeek)->count(),
            'month_orders' => Order::where('placed_at', '>=', $thisMonth)->count(),
            
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            
            // Revenue (in cents)
            'total_revenue' => Order::where('status', '!=', 'refunded')->sum('total'),
            'today_revenue' => Order::where('placed_at', '>=', $today)
                ->where('status', '!=', 'refunded')->sum('total'),
            'month_revenue' => Order::where('placed_at', '>=', $thisMonth)
                ->where('status', '!=', 'refunded')->sum('total'),
        ];

        // Product statistics
        $stats['total_products'] = Product::count();
        $stats['low_stock_products'] = Product::where('stock_quantity', '<=', 10)
            ->where('stock_quantity', '>', 0)->count();
        $stats['out_of_stock'] = Product::where('stock_quantity', '<=', 0)->count();

        // User statistics
        $stats['total_users'] = User::count();
        $stats['admin_users'] = User::where('is_admin', true)->count();

        // Recent orders (Episode 2: potential N+1 if not careful in view)
        $recentOrders = Order::with('user')
            ->orderBy('placed_at', 'desc')
            ->limit(10)
            ->get();

        // Low stock products
        $lowStockProducts = Product::where('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity', 'asc')
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
