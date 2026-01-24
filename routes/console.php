<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Episode 7: Scheduled tasks with timezone considerations
Schedule::command('reports:daily')
    ->dailyAt('09:00')
    // BUG: No explicit timezone - uses server timezone
    ->appendOutputTo(storage_path('logs/scheduled.log'));

// CORRECT would be:
// Schedule::command('reports:daily')
//     ->dailyAt('09:00')
//     ->timezone('America/New_York')
//     ->appendOutputTo(storage_path('logs/scheduled.log'));
