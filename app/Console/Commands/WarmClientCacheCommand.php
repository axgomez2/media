<?php

namespace App\Console\Commands;

use App\Models\ClientUser;
use App\Services\ClientStatisticsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WarmClientCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm-clients
                            {--limit=100 : Number of clients to warm cache for}
                            {--stats : Warm statistics cache}
                            {--all : Warm cache for all clients}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm cache for client management data';

    /**
     * Execute the console command.
     */
    public function handle(ClientStatisticsService $statisticsService): int
    {
        $this->info('Starting client cache warming...');

        if ($this->option('stats')) {
            $this->warmStatisticsCache($statisticsService);
        }

        if ($this->option('all')) {
            $this->warmAllClientsCache();
        } else {
            $limit = (int) $this->option('limit');
            $this->warmTopClientsCache($limit);
        }

        $this->info('Client cache warming completed successfully!');
        return 0;
    }

    /**
     * Warm statistics cache
     */
    private function warmStatisticsCache(ClientStatisticsService $statisticsService): void
    {
        $this->info('Warming statistics cache...');

        // Warm main statistics
        $statisticsService->getClientStats();
        $this->line('✓ Main client statistics cached');

        // Warm activity statistics
        $statisticsService->getClientActivityStats();
        $this->line('✓ Client activity statistics cached');

        // Warm top clients
        $statisticsService->getTopClientsBySpending(10);
        $this->line('✓ Top clients by spending cached');

        // Warm abandoned cart statistics
        $statisticsService->getAbandonedCartStats();
        $this->line('✓ Abandoned cart statistics cached');

        // Warm period statistics
        $periods = ['today', 'week', 'month', 'year'];
        foreach ($periods as $period) {
            $statisticsService->getClientStatsByPeriod($period);
            $this->line("✓ Statistics for period '{$period}' cached");
        }
    }

    /**
     * Warm cache for top clients (most active/valuable)
     */
    private function warmTopClientsCache(int $limit): void
    {
        $this->info("Warming cache for top {$limit} clients...");

        // Get most active clients (those with recent orders)
        $activeClients = ClientUser::with(['orders', 'cart', 'wishlists'])
            ->whereHas('orders', function ($query) {
                $query->where('created_at', '>=', now()->subMonths(3));
            })
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();

        $bar = $this->output->createProgressBar($activeClients->count());
        $bar->start();

        foreach ($activeClients as $client) {
            // Access cached attributes to warm them
            $client->total_orders;
            $client->total_spent;
            $client->cart_items_count;
            $client->has_abandoned_cart;
            $client->cart_total;
            $client->wishlist_items_count;

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("✓ Cache warmed for {$activeClients->count()} active clients");
    }

    /**
     * Warm cache for all clients (use with caution)
     */
    private function warmAllClientsCache(): void
    {
        $this->warn('Warming cache for ALL clients. This may take a while...');

        if (!$this->confirm('Are you sure you want to continue?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $totalClients = ClientUser::count();
        $this->info("Processing {$totalClients} clients...");

        ClientUser::with(['orders', 'cart', 'wishlists'])
            ->chunk(50, function ($clients) {
                $bar = $this->output->createProgressBar($clients->count());
                $bar->start();

                foreach ($clients as $client) {
                    // Access cached attributes to warm them
                    $client->total_orders;
                    $client->total_spent;
                    $client->cart_items_count;
                    $client->has_abandoned_cart;
                    $client->cart_total;
                    $client->wishlist_items_count;

                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
            });

        $this->line("✓ Cache warmed for all {$totalClients} clients");
    }
}
