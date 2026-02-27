<?php

namespace App\Console\Commands;

use App\Jobs\GenerateBulkReceiptsJob;
use Illuminate\Console\Command;

class DemoMemoryLeak extends Command
{
    protected $signature = 'demo:memory-leak {--memory=108M : PHP memory limit for the demo}';

    protected $description = 'Run the bulk receipts memory demo with a constrained memory limit';

    public function handle(): int
    {
        $memoryLimit = (string) $this->option('memory');
        ini_set('memory_limit', $memoryLimit);

        $this->info("Memory limit set to {$memoryLimit}");
        $this->info('Starting bulk receipt generation...');
        $this->info('If memory is bounded, the job should complete without crashing.');
        $this->newLine();

        (new GenerateBulkReceiptsJob())->handle();

        $this->newLine();
        $this->info('Demo finished. Check laravel.log for memory usage progress.');

        return self::SUCCESS;
    }
}
