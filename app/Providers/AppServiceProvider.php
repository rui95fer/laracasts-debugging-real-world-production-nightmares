<?php

namespace App\Providers;

use App\Models\Order;
use App\Policies\OrderPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Order::class, OrderPolicy::class);

        // Use Tailwind pagination
        Paginator::useTailwind();

        // Custom Blade components
        Blade::component('app-layout', \App\View\Components\AppLayout::class);
        Blade::component('admin-layout', \App\View\Components\AdminLayout::class);

        // Episode 2: Query logging for debugging N+1 issues
        if (config('app.debug') && app()->environment('local')) {
            \DB::listen(function ($query) {
                // Log slow queries (over 100ms)
                if ($query->time > 100) {
                    \Log::warning('Slow query detected', [
                        'sql' => $query->sql,
                        'time' => $query->time . 'ms',
                        'bindings' => $query->bindings,
                    ]);
                }
            });
        }
    }
}
