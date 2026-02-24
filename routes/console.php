<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('race:test-users {--count=5} {--password=password}', function () {
    $count = max((int) $this->option('count'), 1);
    $password = (string) $this->option('password');

    $created = 0;
    $updated = 0;

    for ($i = 0; $i < $count; $i++) {
        $email = "racetest{$i}@example.com";
        $name = "Race Test {$i}";

        $user = User::firstOrNew(['email' => $email]);

        if ($user->exists) {
            $user->name = $name;
            $user->password = Hash::make($password);
            $user->save();
            $updated++;
            continue;
        }

        $user->name = $name;
        $user->password = Hash::make($password);
        $user->save();
        $created++;
    }

    $this->info("Race test users ready. Created: {$created}, Updated: {$updated}");
})->purpose('Create/update racetest users for concurrency script');

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
