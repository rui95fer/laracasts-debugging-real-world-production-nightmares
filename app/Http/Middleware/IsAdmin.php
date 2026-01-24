<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * IsAdmin Middleware
 * 
 * Episode 3: The Authorization Hole Nobody Noticed
 * 
 * This middleware should be applied to all admin routes,
 * but Episode 3 demonstrates what happens when it's forgotten
 * on sensitive routes like the refund endpoint.
 */
class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please log in to access this area.');
        }

        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
