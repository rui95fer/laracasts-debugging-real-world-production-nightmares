<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Query Logger Middleware
 * 
 * Episode 2: N+1 Query Detection
 * 
 * This middleware logs all database queries for a request,
 * helping to identify N+1 query problems.
 */
class QueryLogger
{
    protected array $queries = [];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enable in development
        if (!app()->environment('local')) {
            return $next($request);
        }

        // Start listening for queries
        $this->queries = [];
        
        DB::listen(function ($query) {
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ];
        });

        $response = $next($request);

        // Log query summary
        $queryCount = count($this->queries);
        $totalTime = array_sum(array_column($this->queries, 'time'));

        if ($queryCount > 10) {
            Log::warning("High query count detected", [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'query_count' => $queryCount,
                'total_time_ms' => round($totalTime, 2),
            ]);
        }

        // Add debug header
        if (config('app.debug')) {
            $response->headers->set('X-Query-Count', $queryCount);
            $response->headers->set('X-Query-Time', round($totalTime, 2) . 'ms');
        }

        return $response;
    }

    /**
     * Get the collected queries
     */
    public function getQueries(): array
    {
        return $this->queries;
    }
}
