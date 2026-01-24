<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Export Orders to CSV Job
 * 
 * Episode 6: Demonstrates memory-safe vs memory-unsafe patterns
 */
class ExportOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected ?string $startDate;
    protected ?string $endDate;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, ?string $startDate = null, ?string $endDate = null)
    {
        $this->userId = $userId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Execute the job - BUGGY VERSION with memory issues
     * 
     * ============================================
     * EPISODE 6 BUG: Memory-unsafe export
     * ============================================
     */
    public function handle(): void
    {
        Log::info("Starting order export", [
            'user_id' => $this->userId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'memory_start' => $this->formatBytes(memory_get_usage(true)),
        ]);

        $query = Order::with('user', 'items.product');

        if ($this->startDate) {
            $query->where('placed_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->where('placed_at', '<=', $this->endDate);
        }

        // ============================================
        // BUG: Loading all orders at once
        // For large exports, this can crash
        // ============================================
        $orders = $query->get();

        // Build CSV in memory (another memory issue)
        $csv = "Order Number,Date,Customer,Email,Items,Subtotal,Tax,Total,Status\n";

        foreach ($orders as $order) {
            $itemCount = $order->items->count();
            
            // ============================================
            // BUG: String concatenation in loop
            // ============================================
            $csv .= implode(',', [
                $order->order_number,
                $order->placed_at->format('Y-m-d H:i:s'),
                '"' . str_replace('"', '""', $order->user->name) . '"',
                $order->user->email,
                $itemCount,
                $order->subtotal / 100,
                $order->tax / 100,
                $order->total / 100,
                $order->status,
            ]) . "\n";

            // Episode 8 BUG: Logging each row
            Log::debug("Exported order", [
                'order_id' => $order->id,
            ]);
        }

        // Save to storage
        $filename = "exports/orders-{$this->userId}-" . now()->format('Y-m-d-His') . ".csv";
        Storage::put($filename, $csv);

        Log::info("Order export complete", [
            'filename' => $filename,
            'order_count' => $orders->count(),
            'memory_end' => $this->formatBytes(memory_get_usage(true)),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
        ]);

        // In real app, would notify user that export is ready
    }

    /**
     * Memory-safe version of export (CORRECT implementation)
     * 
     * Use this pattern for large exports
     */
    public function handleMemorySafe(): void
    {
        $filename = "exports/orders-{$this->userId}-" . now()->format('Y-m-d-His') . ".csv";
        $path = storage_path("app/{$filename}");

        // Open file handle for streaming writes
        $handle = fopen($path, 'w');
        
        // Write header
        fputcsv($handle, [
            'Order Number', 'Date', 'Customer', 'Email', 
            'Items', 'Subtotal', 'Tax', 'Total', 'Status'
        ]);

        $query = Order::with('user', 'items');

        if ($this->startDate) {
            $query->where('placed_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->where('placed_at', '<=', $this->endDate);
        }

        // CORRECT: Use lazy() or chunk() to process in batches
        $query->lazy(100)->each(function ($order) use ($handle) {
            fputcsv($handle, [
                $order->order_number,
                $order->placed_at->format('Y-m-d H:i:s'),
                $order->user->name,
                $order->user->email,
                $order->items->count(),
                $order->subtotal / 100,
                $order->tax / 100,
                $order->total / 100,
                $order->status,
            ]);
            
            // Explicitly unset to help GC
            unset($order);
        });

        fclose($handle);

        Log::info("Memory-safe export complete", [
            'filename' => $filename,
            'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
        ]);
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
