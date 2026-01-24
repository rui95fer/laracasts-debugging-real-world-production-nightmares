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
 * Generate Receipt PDF Job
 * 
 * Episode 6: The Memory Leak That Kills Queue Workers
 * 
 * This job demonstrates memory leak patterns when
 * processing large numbers of orders.
 */
class GenerateReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Generating receipt for order {$this->order->order_number}");

        // Load the order with relationships
        $order = $this->order->load('user', 'items.product');

        // Generate PDF content (simplified - in real app use DomPDF)
        $content = $this->generateReceiptContent($order);

        // Save to storage
        $filename = "receipts/receipt-{$order->order_number}.html";
        Storage::put($filename, $content);

        Log::info("Receipt generated: {$filename}");
    }

    /**
     * Generate receipt HTML content
     */
    protected function generateReceiptContent(Order $order): string
    {
        $html = "<!DOCTYPE html><html><head><title>Receipt</title></head><body>";
        $html .= "<h1>Order Receipt</h1>";
        $html .= "<p>Order: {$order->order_number}</p>";
        $html .= "<p>Date: {$order->placed_at}</p>";
        $html .= "<p>Customer: {$order->user->name}</p>";
        
        $html .= "<table><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>";
        
        foreach ($order->items as $item) {
            $html .= "<tr>";
            $html .= "<td>{$item->product->name}</td>";
            $html .= "<td>{$item->quantity}</td>";
            $html .= "<td>{$item->formatted_price}</td>";
            $html .= "<td>{$item->formatted_subtotal}</td>";
            $html .= "</tr>";
        }
        
        $html .= "</table>";
        $html .= "<p>Subtotal: {$order->formatted_subtotal}</p>";
        $html .= "<p>Tax: {$order->formatted_tax}</p>";
        $html .= "<p><strong>Total: {$order->formatted_total}</strong></p>";
        $html .= "</body></html>";

        return $html;
    }
}
