<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct()
    {
        // Note: The 'admin' middleware should be applied in routes
        // Episode 3 shows what happens when it's missing
    }

    /**
     * Display all orders
     * 
     * ============================================
     * EPISODE 2 BUG: N+1 Query Problem!
     * ============================================
     * 
     * This loads orders without eager loading relationships.
     * When the view accesses $order->user, $order->items, etc.,
     * it triggers N+1 queries!
     * 
     * With 5,000 orders, this can generate 20,000+ queries
     * and take 30+ seconds to load.
     */
    public function index(Request $request)
    {
        // Episode 8 BUG: Logging on every page view
        Log::debug('Admin viewing orders', [
            'admin_id' => auth()->id(),
            'page' => $request->get('page', 1),
        ]);

        // ============================================
        // EPISODE 2 BUG: No eager loading!
        // ============================================
        $orders = Order::latest('placed_at')
            ->paginate(50);
        
        // When the view loops through $orders and accesses:
        // - $order->user->name     → N+1 queries
        // - $order->items          → N+1 queries
        // - $item->product->name   → N+1 queries
        // - $product->category     → N+1 queries

        return view('admin.orders.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display single order
     */
    public function show(Order $order)
    {
        // ============================================
        // EPISODE 2: Even single order has N+1 potential
        // ============================================
        // We don't eager load here, so accessing items in view
        // will trigger additional queries
        
        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,refunded',
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // Episode 8 BUG: Logging status changes
        Log::info("Order status updated", [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $order->status,
            'admin_id' => auth()->id(),
        ]);

        return back()->with('success', 'Order status updated.');
    }

    /**
     * Export orders to CSV
     * 
     * Episode 6: This could trigger memory issues with large datasets
     */
    public function export(Request $request)
    {
        // Dispatch export job
        \App\Jobs\ExportOrdersJob::dispatch(
            auth()->id(),
            $request->get('start_date'),
            $request->get('end_date')
        );

        return back()->with('info', 'Export started. You will be notified when ready.');
    }

    /**
     * Dashboard with order statistics
     * 
     * Episode 7 BUG: Timezone issues in date filtering
     */
    public function dashboard(Request $request)
    {
        // ============================================
        // EPISODE 7 BUG: Timezone-naive date filtering
        // ============================================
        
        // BUG: Using today() without considering user timezone
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // These counts might be wrong for admins in different timezones
        $todayOrders = Order::where('placed_at', '>=', $today)->count();
        $weekOrders = Order::where('placed_at', '>=', $thisWeek)->count();
        $monthOrders = Order::where('placed_at', '>=', $thisMonth)->count();

        // BUG: Revenue calculations with potential float issues (Episode 10)
        $todayRevenue = Order::where('placed_at', '>=', $today)->sum('total');
        $weekRevenue = Order::where('placed_at', '>=', $thisWeek)->sum('total');
        $monthRevenue = Order::where('placed_at', '>=', $thisMonth)->sum('total');

        return view('admin.dashboard.index', [
            'todayOrders' => $todayOrders,
            'weekOrders' => $weekOrders,
            'monthOrders' => $monthOrders,
            'todayRevenue' => $todayRevenue,
            'weekRevenue' => $weekRevenue,
            'monthRevenue' => $monthRevenue,
        ]);
    }
}
