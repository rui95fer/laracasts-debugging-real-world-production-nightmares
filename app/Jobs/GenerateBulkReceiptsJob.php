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
     * Execute the job using a memory-safe iteration strategy.
     */
    public function handle(): void
    {
        $startMemory = memory_get_usage(true);
        $processed = 0;
        $chunkSize = 100;

        Log::info("Starting bulk receipt generation", [
            'memory_start' => $this->formatBytes($startMemory),
            'chunk_size' => $chunkSize,
        ]);

        Order::query()
            ->with('items.product', 'user')
            ->lazyById($chunkSize)
            ->each(function (Order $order) use (&$processed): void {
            $content = $this->generateReceiptContent($order);

            $filename = "receipts/receipt-{$order->order_number}.html";
            Storage::put($filename, $content);
            unset($content);

            $processed++;

            if ($processed % 100 === 0) {
                Log::info('Bulk receipt progress', [
                    'processed' => $processed,
                    'memory' => $this->formatBytes(memory_get_usage(true)),
                ]);
            }
        });

        Log::info("Bulk receipt generation complete", [
            'processed' => $processed,
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
