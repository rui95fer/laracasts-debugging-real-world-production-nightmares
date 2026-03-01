<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderCalculator;
use Illuminate\Console\Command;

class ReconcileOrders extends Command
{
    protected $signature = 'orders:reconcile
                            {--limit=100 : Number of orders to check}
                            {--show-all : Show all orders, not just mismatches}';

    protected $description = 'Reconcile order totals and find discrepancies';

    public function handle(OrderCalculator $calculator): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $showAll = (bool) $this->option('show-all');

        $this->info("Reconciling {$limit} orders...\n");

        $checked = 0;
        $mismatches = 0;
        $totalDiscrepancy = 0;

        Order::with('items')
            ->latest()
            ->limit($limit)
            ->chunk(50, function ($orders) use ($calculator, &$checked, &$mismatches, &$totalDiscrepancy, $showAll): void {
                foreach ($orders as $order) {
                    $checked++;
                    $verification = $calculator->verifyOrder($order);

                    if (! $verification['matches_correct'] || $showAll) {
                        $discrepancy = $verification['discrepancy'];

                        if (! $verification['matches_correct']) {
                            $mismatches++;
                            $totalDiscrepancy += abs($discrepancy);

                            $this->error(sprintf(
                                '[FAIL] Order #%s: Stored=$%s, Calculated=$%s, Diff=%s cents',
                                $order->order_number,
                                number_format($order->total / 100, 2),
                                number_format($verification['calculated_correct']['total'] / 100, 2),
                                $discrepancy > 0 ? "+{$discrepancy}" : (string) $discrepancy
                            ));
                        } elseif ($showAll) {
                            $this->line(sprintf(
                                '[OK]   Order #%s: $%s',
                                $order->order_number,
                                number_format($order->total / 100, 2)
                            ));
                        }
                    }
                }
            });

        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Orders checked: {$checked}");

        if ($mismatches > 0) {
            $this->error("Mismatches found: {$mismatches}");
            $this->error('Total discrepancy: '.$totalDiscrepancy.' cents ($'.number_format($totalDiscrepancy / 100, 2).')');

            return self::FAILURE;
        }

        $this->info('Mismatches found: 0');
        $this->info('All orders reconcile correctly!');

        return self::SUCCESS;
    }
}
