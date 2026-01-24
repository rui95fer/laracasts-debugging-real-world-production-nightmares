<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user's orders
     */
    public function index()
    {
        $orders = Auth::user()
            ->orders()
            ->with('items.product')
            ->orderBy('placed_at', 'desc')
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display single order
     */
    public function show(Order $order)
    {
        // Check if user owns this order (basic check)
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'You do not have permission to view this order.');
        }

        $order->load('items.product');

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Process refund
     * 
     * ============================================
     * EPISODE 3 BUG: Authorization Hole!
     * ============================================
     * 
     * This method should only be accessible by admins,
     * but there's NO authorization check!
     * 
     * Any authenticated user can call this endpoint
     * and refund any order!
     */
    public function refund(Order $order)
    {
        // ============================================
        // BUG: Missing authorization check!
        // Should have: $this->authorize('refund', $order);
        // Or at minimum: if (!Auth::user()->isAdmin()) abort(403);
        // ============================================

        // Episode 8 BUG: Logging sensitive action without proper context
        Log::info("Processing refund", [
            'order_id' => $order->id,
            'order_total' => $order->total,
            // BUG: Not logging WHO initiated the refund!
        ]);

        if (!$order->canBeRefunded()) {
            return back()->with('error', 'This order cannot be refunded.');
        }

        // Process the refund
        // In a real app, this would integrate with payment gateway
        $order->refund();

        // Episode 8 BUG: More logging without proper audit trail
        Log::info("Refund processed", [
            'order_id' => $order->id,
            // BUG: Still not logging who did it!
        ]);

        return back()->with('success', 'Order has been refunded.');
    }

    /**
     * Download order receipt (triggers PDF generation job)
     */
    public function downloadReceipt(Order $order)
    {
        // Check ownership
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        // Dispatch job to generate PDF
        // Episode 6: This job has memory leak issues
        \App\Jobs\GenerateReceiptJob::dispatch($order);

        return back()->with('info', 'Receipt is being generated. Check back shortly.');
    }
}
