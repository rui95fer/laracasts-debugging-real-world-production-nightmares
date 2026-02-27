<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DemoMemoryLeakUnsafe extends Command
{
    protected $signature = 'demo:memory-leak:unsafe {--memory=108M : PHP memory limit for the demo}';

    protected $description = 'Run an intentionally memory-unsafe bulk receipt demo';

    public function handle(): int
    {
        $memoryLimit = (string) $this->option('memory');
        ini_set('memory_limit', $memoryLimit);

        $this->warn("Memory limit set to {$memoryLimit}");
        $this->warn('Running UNSAFE implementation using get() (expected to grow memory).');
        $this->newLine();

        $this->line('Loading all orders and relations into memory...');
        $orders = Order::with('items.product', 'user')->get();

        $this->line('Starting receipt generation loop...');

        foreach ($orders as $index => $order) {
            $content = $this->generateReceiptContent($order);
            Storage::put("receipts/unsafe-receipt-{$order->order_number}.html", $content);

            if (($index + 1) % 100 === 0) {
                $this->line(sprintf(
                    'Processed %d | Memory: %s',
                    $index + 1,
                    $this->formatBytes(memory_get_usage(true))
                ));
            }
        }

        $this->newLine();
        $this->line('Unsafe demo finished without crash in this run.');
        $this->line('Try a lower --memory value or larger dataset to force exhaustion.');

        return self::SUCCESS;
    }

    protected function generateReceiptContent(Order $order): string
    {
        $html = "<!DOCTYPE html><html><head><title>Receipt - {$order->order_number}</title></head><body>";
        $html .= "<h1>NightmareMart Receipt</h1>";
        $html .= "<p>Order Number: {$order->order_number}</p>";
        $html .= "<p>Date: {$order->placed_at}</p>";
        $html .= "<p>Customer: {$order->user->name}</p>";
        $html .= "<p>Email: {$order->user->email}</p>";
        $html .= "<table><thead><tr><th>Product</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr></thead><tbody>";

        foreach ($order->items as $item) {
            $html .= "<tr>";
            $html .= "<td>{$item->product->name}</td>";
            $html .= "<td>{$item->quantity}</td>";
            $html .= "<td>{$item->formatted_price}</td>";
            $html .= "<td>{$item->formatted_subtotal}</td>";
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= "<p>Subtotal: {$order->formatted_subtotal}</p>";
        $html .= "<p>Tax: {$order->formatted_tax}</p>";
        $html .= "<p>Total: {$order->formatted_total}</p>";
        $html .= '</body></html>';

        return $html;
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
