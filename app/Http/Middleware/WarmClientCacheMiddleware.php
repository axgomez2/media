<?php

namespace App\Http\Middleware;

use App\Services\ClientStatisticsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class WarmClientCacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only warm cache for client management routes
        if ($request->routeIs('admin.reports.clients.*')) {
            $this->warmEssentialCache();
        }

        return $next($request);
    }

    /**
     * Warm essential cache data if not already cached
     */
    private function warmEssentialCache(): void
    {
        // Check if main statistics are cached, if not, warm them
        if (!Cache::has('client_stats')) {
            app(ClientStatisticsService::class)->getClientStats();
        }

        // Warm activity stats if accessing the main index
        if (!Cache::has('client_activity_stats')) {
            app(ClientStatisticsService::class)->getClientActivityStats();
        }
    }
}
