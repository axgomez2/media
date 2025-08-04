<?php

namespace Tests\Feature\Performance;

use App\Models\Address;
use App\Models\ClientUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientManagementPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function client_listing_performs_well_with_large_dataset()
    {
        $this->actingAs($this->adminUser);

        // Create a large dataset
        $startTime = microtime(true);

        ClientUser::factory()->count(1000)->create();

        $creationTime = microtime(true) - $startTime;
        $this->assertLessThan(30, $creationTime, 'Data creation took too long');

        // Test query performance
        $queryStartTime = microtime(true);

        $response = $this->get(route('admin.reports.clients.index'));

        $queryTime = microtime(true) - $queryStartTime;

        $response->assertStatus(200);
        $this->assertLessThan(2, $queryTime, 'Query took longer than 2 seconds');
    }

    /** @test */
    public function client_search_performs_well_with_large_dataset()
    {
        $this->actingAs($this->adminUser);

        // Create clients with searchable names
        ClientUser::factory()->count(500)->create(['name' => 'João Silva']);
        ClientUser::factory()->count(500)->create(['name' => 'Maria Santos']);

        $startTime = microtime(true);

        $response = $this->get(route('admin.reports.clients.index', ['search' => 'João']));

        $executionTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.5, $executionTime, 'Search query took longer than 1.5 seconds');

        $clients = $response->viewData('clients');
        $this->assertEquals(500, $clients->total());
    }

    /** @test */
    public function client_filtering_performs_well_with_large_dataset()
    {
        $this->actingAs($this->adminUser);

        // Create mixed dataset
        ClientUser::factory()->verified()->active()->count(300)->create();
        ClientUser::factory()->unverified()->inactive()->count(200)->create();
        ClientUser::factory()->verified()->inactive()->count(250)->create();
        ClientUser::factory()->unverified()->active()->count(250)->create();

        $startTime = microtime(true);

        $response = $this->get(route('admin.reports.clients.index', [
            'verified' => 'verified',
            'account_status' => 'active'
        ]));

        $executionTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.5, $executionTime, 'Filtered query took longer than 1.5 seconds');

        $clients = $response->viewData('clients');
        $this->assertEquals(300, $clients->total());
    }

    /** @test */
    public function client_detail_view_performs_well_with_related_data()
    {
        $this->actingAs($this->adminUser);

        $client = ClientUser::factory()->create();

        // Create multiple addresses for the client
        Address::factory()->forUser($client)->count(5)->create();

        $startTime = microtime(true);

        $response = $this->get(route('admin.reports.clients.show', $client->id));

        $executionTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1, $executionTime, 'Client detail view took longer than 1 second');
    }

    /** @test */
    public function csv_export_performs_well_with_large_dataset()
    {
        $this->actingAs($this->adminUser);

        // Create a substantial dataset for export
        ClientUser::factory()->count(500)->create();

        // Add addresses to some clients
        $clients = ClientUser::take(100)->get();
        foreach ($clients as $client) {
            Address::factory()->forUser($client)->default()->create();
        }

        $startTime = microtime(true);

        $response = $this->get(route('admin.reports.clients.export'));

        $executionTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(10, $executionTime, 'CSV export took longer than 10 seconds');

        // Verify the export contains data
        $content = $response->getContent();
        $lines = explode("\n", $content);
        $this->assertGreaterThan(500, count($lines), 'Export should contain all clients plus headers');
    }

    /** @test */
    public function statistics_calculation_performs_well_with_large_dataset()
    {
        $this->actingAs($this->adminUser);

        // Create diverse dataset for statistics
        ClientUser::factory()->verified()->newThisMonth()->count(100)->create();
        ClientUser::factory()->unverified()->old()->count(200)->create();
        ClientUser::factory()->verified()->old()->count(300)->create();

        $startTime = microtime(true);

        $response = $this->get(route('admin.reports.clients.index'));

        $executionTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2, $executionTime, 'Statistics calculation took longer than 2 seconds');

        $stats = $response->viewData('stats');
        $this->assertEquals(600, $stats['total_clients']);
        $this->assertEquals(100, $stats['new_this_month']);
        $this->assertEquals(400, $stats['verified_clients']);
    }

    /** @test */
    public function pagination_performs_well_with_large_dataset()
    {
        $this->actingAs($this->adminUser);

        ClientUser::factory()->count(2000)->create();

        // Test first page
        $startTime = microtime(true);
        $response = $this->get(route('admin.reports.clients.index', ['page' => 1]));
        $firstPageTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.5, $firstPageTime, 'First page took longer than 1.5 seconds');

        // Test middle page
        $startTime = microtime(true);
        $response = $this->get(route('admin.reports.clients.index', ['page' => 20]));
        $middlePageTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.5, $middlePageTime, 'Middle page took longer than 1.5 seconds');

        // Test last page
        $startTime = microtime(true);
        $response = $this->get(route('admin.reports.clients.index', ['page' => 40]));
        $lastPageTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.5, $lastPageTime, 'Last page took longer than 1.5 seconds');
    }

    /** @test */
    public function concurrent_status_updates_perform_well()
    {
        $this->actingAs($this->adminUser);

        $clients = ClientUser::factory()->active()->count(10)->create();

        $startTime = microtime(true);

        // Simulate concurrent status updates
        foreach ($clients as $client) {
            $response = $this->putJson(route('admin.reports.clients.updateStatus', $client->id), [
                'status' => 'inactive',
                'reason' => 'Bulk update test'
            ]);

            $response->assertStatus(200);
        }

        $executionTime = microtime(true) - $startTime;

        $this->assertLessThan(5, $executionTime, 'Bulk status updates took longer than 5 seconds');

        // Verify all updates were successful
        $this->assertEquals(0, ClientUser::where('status', 'active')->count());
        $this->assertEquals(10, ClientUser::where('status', 'inactive')->count());
    }

    /** @test */
    public function database_queries_are_optimized_for_client_listing()
    {
        $this->actingAs($this->adminUser);

        ClientUser::factory()->count(100)->create();

        // Enable query logging
        DB::enableQueryLog();

        $response = $this->get(route('admin.reports.clients.index'));

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);

        // Should not have N+1 query problems
        $this->assertLessThan(10, count($queries), 'Too many database queries executed');

        // Check for efficient queries (no SELECT * without WHERE/LIMIT)
        foreach ($queries as $query) {
            if (strpos($query['query'], 'select *') !== false) {
                $this->assertTrue(
                    strpos($query['query'], 'limit') !== false ||
                    strpos($query['query'], 'where') !== false,
                    'Inefficient SELECT * query without WHERE or LIMIT: ' . $query['query']
                );
            }
        }
    }

    /** @test */
    public function memory_usage_is_reasonable_with_large_dataset()
    {
        $this->actingAs($this->adminUser);

        $initialMemory = memory_get_usage(true);

        ClientUser::factory()->count(1000)->create();

        $afterCreationMemory = memory_get_usage(true);

        $response = $this->get(route('admin.reports.clients.index'));

        $afterQueryMemory = memory_get_usage(true);

        $response->assertStatus(200);

        $memoryIncrease = $afterQueryMemory - $afterCreationMemory;
        $memoryIncreaseMB = $memoryIncrease / 1024 / 1024;

        $this->assertLessThan(50, $memoryIncreaseMB, 'Query increased memory usage by more than 50MB');
    }

    /** @test */
    public function complex_search_with_multiple_filters_performs_well()
    {
        $this->actingAs($this->adminUser);

        // Create complex dataset
        ClientUser::factory()->verified()->active()->newThisMonth()->count(100)->create(['name' => 'João Silva']);
        ClientUser::factory()->unverified()->inactive()->old()->count(200)->create(['name' => 'Maria Santos']);
        ClientUser::factory()->verified()->inactive()->old()->count(150)->create(['name' => 'Pedro Costa']);
        ClientUser::factory()->unverified()->active()->newThisMonth()->count(50)->create(['name' => 'Ana Oliveira']);

        $startTime = microtime(true);

        $response = $this->get(route('admin.reports.clients.index', [
            'search' => 'João',
            'verified' => 'verified',
            'account_status' => 'active',
            'period' => 'month',
            'per_page' => 25
        ]));

        $executionTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2, $executionTime, 'Complex filtered search took longer than 2 seconds');

        $clients = $response->viewData('clients');
        $this->assertEquals(100, $clients->total());
        $this->assertEquals(25, $clients->perPage());
    }
}
