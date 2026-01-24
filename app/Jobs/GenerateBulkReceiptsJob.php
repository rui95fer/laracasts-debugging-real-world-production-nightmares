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
 * Generate Bulk Receipts Job
 * 
 * ============================================
 * EPISODE 6 BUG: Memory Leak Pattern
 * ============================================
 * 
 * This job loads ALL orders into memory at once,
 * generates PDFs in a loop without releasing memory,
 * and will crash with "Allowed memory size exhausted"
 * after processing ~50-100 orders.
 */
class GenerateBulkReceiptsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     * 
     * BUG: This will crash with memory exhaustion!
     */
    public function handle(): void
    {
        $startMemory = memory_get_usage(true);
        Log::info("Starting bulk receipt generation", [
            'memory_start' => $this->formatBytes($startMemory),
        ]);

        // ============================================
        // BUG 1: Loading ALL orders into memory at once
        // With 5,000+ orders, this alone can use 100MB+
        // ============================================
        $orders = Order::with('items.product', 'user')->get();

        Log::info("Loaded orders", [
            'count' => $orders->count(),
            'memory_after_load' => $this->formatBytes(memory_get_usage(true)),
        ]);

        foreach ($orders as $order) {
            // ============================================
            // BUG 2: PDF generation creates memory pressure
            // Each iteration adds memory that isn't released
            // ============================================
            $content = $this->generateReceiptContent($order);

            // ============================================
            // BUG 3: Storing content in variable keeps it in memory
            // ============================================
            $filename = "receipts/receipt-{$order->order_number}.html";
            Storage::put($filename, $content);

            // ============================================
            // BUG 4: Logging in loop adds memory pressure
            // ============================================
            Log::debug("Generated receipt", [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'memory' => $this->formatBytes(memory_get_usage(true)),
            ]);

            // ============================================
            // BUG 5: $order object never explicitly released
            // PHP's GC can't keep up with the allocation rate
            // ============================================
        }

        // This line is often never reached due to memory crash
        Log::info("Bulk receipt generation complete", [
            'memory_end' => $this->formatBytes(memory_get_usage(true)),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
        ]);
    }

    /**
     * Generate receipt HTML content
     */
    protected function generateReceiptContent(Order $order): string
    {
        // ============================================
        // BUG 6: Building large strings in memory
        // ============================================
        $html = "<!DOCTYPE html><html><head><title>Receipt - {$order->order_number}</title>";
        $html .= "<style>body{font-family:Arial,sans-serif;}</style></head><body>";
        $html .= "<h1>NightmareMart Receipt</h1>";
        $html .= "<hr>";
        $html .= "<p><strong>Order Number:</strong> {$order->order_number}</p>";
        $html .= "<p><strong>Date:</strong> {$order->placed_at}</p>";
        $html .= "<p><strong>Customer:</strong> {$order->user->name}</p>";
        $html .= "<p><strong>Email:</strong> {$order->user->email}</p>";
        $html .= "<hr>";
        
        $html .= "<table style='width:100%;border-collapse:collapse;'>";
        $html .= "<thead><tr style='background:#333;color:#fff;'>";
        $html .= "<th style='padding:10px;'>Product</th>";
        $html .= "<th style='padding:10px;'>Quantity</th>";
        $html .= "<th style='padding:10px;'>Unit Price</th>";
        $html .= "<th style='padding:10px;'>Subtotal</th>";
        $html .= "</tr></thead><tbody>";
        
        foreach ($order->items as $item) {
            $html .= "<tr style='border-bottom:1px solid #ccc;'>";
            $html .= "<td style='padding:10px;'>{$item->product->name}</td>";
            $html .= "<td style='padding:10px;text-align:center;'>{$item->quantity}</td>";
            $html .= "<td style='padding:10px;text-align:right;'>{$item->formatted_price}</td>";
            $html .= "<td style='padding:10px;text-align:right;'>{$item->formatted_subtotal}</td>";
            $html .= "</tr>";
        }
        
        $html .= "</tbody></table>";
        $html .= "<hr>";
        $html .= "<div style='text-align:right;'>";
        $html .= "<p><strong>Subtotal:</strong> {$order->formatted_subtotal}</p>";
        $html .= "<p><strong>Tax:</strong> {$order->formatted_tax}</p>";
        $html .= "<p style='font-size:1.5em;'><strong>Total:</strong> {$order->formatted_total}</p>";
        $html .= "</div>";
        $html .= "<hr>";
        $html .= "<p style='text-align:center;color:#666;'>Thank you for shopping at NightmareMart!</p>";
        $html .= "</body></html>";

        return $html;
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
