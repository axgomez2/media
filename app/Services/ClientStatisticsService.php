<?php

namespace App\Services;

use App\Models\ClientUser;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClientStatisticsService
{
    /**
     * Cache duration in minutes
     */
    private const CACHE_DURATION = 15; // 15 minutes

    /**
     * Get client statistics with caching
     */
    public function getClientStats(): array
    {
        return Cache::remember('client_stats', self::CACHE_DURATION * 60, function () {
            return $this->calculateClientStats();
        });
    }

    /**
     * Get client statistics for a specific period with caching
     */
    public function getClientStatsByPeriod(string $period): array
    {
        $cacheKey = "client_stats_period_{$period}";

        return Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($period) {
            return $this->calculateClientStatsByPeriod($period);
        });
    }

    /**
     * Calculate client statistics using optimized queries
     */
    private function calculateClientStats(): array
    {
        // Use a single query to get multiple counts
        $clientCounts = DB::table('client_users')
            ->selectRaw('
                COUNT(*) as total_clients,
                COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_clients,
                COUNT(CASE WHEN status = "active" THEN 1 END) as active_clients,
                COUNT(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN 1 END) as new_this_month,
                COUNT(CASE WHEN updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as active_last_30_days
            ')
            ->first();

        // Get clients with orders count using a separate optimized query
        $clientsWithOrders = DB::table('client_users')
            ->join('orders', 'client_users.id', '=', 'orders.user_id')
            ->distinct('client_users.id')
            ->count();

        // Get average value per client using optimized query
        $averageValuePerClient = DB::table('orders')
            ->whereIn('status', ['delivered', 'payment_approved'])
            ->selectRaw('user_id, SUM(total) as total_spent')
            ->groupBy('user_id')
            ->get()
            ->avg('total_spent') ?? 0;

        // Calculate conversion rate
        $conversionRate = $clientCounts->total_clients > 0
            ? ($clientsWithOrders / $clientCounts->total_clients) * 100
            : 0;

        // Calculate verification rate
        $verificationRate = $clientCounts->total_clients > 0
            ? ($clientCounts->verified_clients / $clientCounts->total_clients) * 100
            : 0;

        return [
            'total_clients' => (int) $clientCounts->total_clients,
            'new_this_month' => (int) $clientCounts->new_this_month,
            'verified_clients' => (int) $clientCounts->verified_clients,
            'verification_rate' => round($verificationRate, 2),
            'active_clients' => (int) $clientCounts->active_last_30_days,
            'clients_with_orders' => (int) $clientsWithOrders,
            'conversion_rate' => round($conversionRate, 2),
            'average_value_per_client' => round($averageValuePerClient, 2)
        ];
    }

    /**
     * Calculate client statistics for a specific period
     */
    private function calculateClientStatsByPeriod(string $period): array
    {
        $dateCondition = $this->getDateConditionForPeriod($period);

        $clientCounts = DB::table('client_users')
            ->whereRaw($dateCondition)
            ->selectRaw('
                COUNT(*) as total_clients,
                COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_clients,
                COUNT(CASE WHEN status = "active" THEN 1 END) as active_clients
            ')
            ->first();

        return [
            'total_clients' => (int) $clientCounts->total_clients,
            'verified_clients' => (int) $clientCounts->verified_clients,
            'active_clients' => (int) $clientCounts->active_clients,
            'verification_rate' => $clientCounts->total_clients > 0
                ? round(($clientCounts->verified_clients / $clientCounts->total_clients) * 100, 2)
                : 0
        ];
    }

    /**
     * Get date condition SQL for different periods
     */
    private function getDateConditionForPeriod(string $period): string
    {
        return match ($period) {
            'today' => 'DATE(created_at) = CURDATE()',
            'week' => 'created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)',
            'month' => 'MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())',
            'year' => 'YEAR(created_at) = YEAR(NOW())',
            default => '1=1' // All time
        };
    }

    /**
     * Get client activity statistics with caching
     */
    public function getClientActivityStats(): array
    {
        return Cache::remember('client_activity_stats', self::CACHE_DURATION * 60, function () {
            return $this->calculateClientActivityStats();
        });
    }

    /**
     * Calculate client activity statistics
     */
    private function calculateClientActivityStats(): array
    {
        // Get registration trends for the last 12 months
        $registrationTrends = DB::table('client_users')
            ->selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as count
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => sprintf('%04d-%02d', $item->year, $item->month),
                    'count' => (int) $item->count
                ];
            });

        // Get order frequency statistics
        $orderStats = DB::table('orders')
            ->selectRaw('user_id, COUNT(*) as order_count')
            ->groupBy('user_id')
            ->get();

        $orderFrequency = (object) [
            'clients_with_orders' => $orderStats->count(),
            'avg_orders_per_client' => $orderStats->count() > 0 ? $orderStats->avg('order_count') : 0
        ];

        return [
            'registration_trends' => $registrationTrends->toArray(),
            'clients_with_orders' => (int) ($orderFrequency->clients_with_orders ?? 0),
            'avg_orders_per_client' => round($orderFrequency->avg_orders_per_client ?? 0, 2)
        ];
    }

    /**
     * Clear all client statistics cache
     */
    public function clearCache(): void
    {
        $cacheKeys = [
            'client_stats',
            'client_activity_stats',
            'client_stats_period_today',
            'client_stats_period_week',
            'client_stats_period_month',
            'client_stats_period_year'
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get top clients by spending with caching
     */
    public function getTopClientsBySpending(int $limit = 10): array
    {
        $cacheKey = "top_clients_spending_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($limit) {
            return DB::table('client_users')
                ->join('orders', 'client_users.id', '=', 'orders.user_id')
                ->select([
                    'client_users.id',
                    'client_users.name',
                    'client_users.email',
                    DB::raw('SUM(orders.total) as total_spent'),
                    DB::raw('COUNT(orders.id) as total_orders')
                ])
                ->whereIn('orders.status', ['delivered', 'payment_approved'])
                ->groupBy('client_users.id', 'client_users.name', 'client_users.email')
                ->orderByDesc('total_spent')
                ->limit($limit)
                ->get()
                ->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'name' => $client->name,
                        'email' => $client->email,
                        'total_spent' => (float) $client->total_spent,
                        'total_orders' => (int) $client->total_orders,
                        'avg_order_value' => $client->total_orders > 0
                            ? round($client->total_spent / $client->total_orders, 2)
                            : 0
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get abandoned cart statistics with caching
     */
    public function getAbandonedCartStats(): array
    {
        return Cache::remember('abandoned_cart_stats', self::CACHE_DURATION * 60, function () {
            $abandonedCarts = DB::table('carts')
                ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
                ->where('carts.updated_at', '<=', now()->subDays(7))
                ->selectRaw('
                    COUNT(DISTINCT carts.id) as abandoned_carts_count,
                    COUNT(DISTINCT carts.user_id) as clients_with_abandoned_carts,
                    SUM(cart_items.quantity) as total_abandoned_items
                ')
                ->first();

            return [
                'abandoned_carts_count' => (int) ($abandonedCarts->abandoned_carts_count ?? 0),
                'clients_with_abandoned_carts' => (int) ($abandonedCarts->clients_with_abandoned_carts ?? 0),
                'total_abandoned_items' => (int) ($abandonedCarts->total_abandoned_items ?? 0)
            ];
        });
    }
}
