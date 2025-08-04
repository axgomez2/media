<?php

namespace Tests\Feature\Admin;

use App\Models\Address;
use App\Models\ClientUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for authentication
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function admin_can_access_client_reports_index()
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.reports.clients.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.clients.index');
        $response->assertViewHas(['clients', 'stats']);
    }

    /** @test */
    public function guest_cannot_access_client_reports()
    {
        $response = $this->get(route('admin.reports.clients.index'));

        $response->assertRedirect(); // Should redirect to login
    }

    /** @test */
    public function admin_can_view_clients_list_with_pagination()
    {
        $this->actingAs($this->adminUser);

        // Create multiple clients
        ClientUser::factory()->count(75)->create();

        $response = $this->get(route('admin.reports.clients.index'));

        $response->assertStatus(200);
        $response->assertViewHas('clients');

        $clients = $response->viewData('clients');
        $this->assertEquals(50, $clients->perPage()); // Default per page
        $this->assertTrue($clients->hasPages());
    }

    /** @test */
    public function admin_can_search_clients_by_name()
    {
        $this->actingAs($this->adminUser);

        $targetClient = ClientUser::factory()->create(['name' => 'João Silva']);
        ClientUser::factory()->create(['name' => 'Maria Santos']);

        $response = $this->get(route('admin.reports.clients.index', ['search' => 'João']));

        $response->assertStatus(200);
        $clients = $response->viewData('clients');

        $this->assertCount(1, $clients->items());
        $this->assertEquals('João Silva', $clients->items()[0]->name);
    }

    /** @test */
    public function admin_can_search_clients_by_email()
    {
        $this->actingAs($this->adminUser);

        $targetClient = ClientUser::factory()->create(['email' => 'joao@example.com']);
        ClientUser::factory()->create(['email' => 'maria@example.com']);

        $response = $this->get(route('admin.reports.clients.index', ['search' => 'joao@example.com']));

        $response->assertStatus(200);
        $clients = $response->viewData('clients');

        $this->assertCount(1, $clients->items());
        $this->assertEquals('joao@example.com', $clients->items()[0]->email);
    }

    /** @test */
    public function admin_can_filter_clients_by_verification_status()
    {
        $this->actingAs($this->adminUser);

        $verifiedClient = ClientUser::factory()->verified()->create();
        $unverifiedClient = ClientUser::factory()->unverified()->create();

        // Filter for verified clients
        $response = $this->get(route('admin.reports.clients.index', ['verified' => 'verified']));

        $response->assertStatus(200);
        $clients = $response->viewData('clients');

        $this->assertCount(1, $clients->items());
        $this->assertNotNull($clients->items()[0]->email_verified_at);

        // Filter for unverified clients
        $response = $this->get(route('admin.reports.clients.index', ['verified' => 'unverified']));

        $response->assertStatus(200);
        $clients = $response->viewData('clients');

        $this->assertCount(1, $clients->items());
        $this->assertNull($clients->items()[0]->email_verified_at);
    }

    /** @test */
    public function admin_can_filter_clients_by_account_status()
    {
        $this->actingAs($this->adminUser);

        $activeClient = ClientUser::factory()->active()->create();
        $inactiveClient = ClientUser::factory()->inactive()->create();

        // Filter for active clients
        $response = $this->get(route('admin.reports.clients.index', ['account_status' => 'active']));

        $response->assertStatus(200);
        $clients = $response->viewData('clients');

        $this->assertCount(1, $clients->items());
        $this->assertEquals('active', $clients->items()[0]->status);
    }

    /** @test */
    public function admin_can_filter_clients_by_registration_period()
    {
        $this->actingAs($this->adminUser);

        $newClient = ClientUser::factory()->newThisMonth()->create();
        $oldClient = ClientUser::factory()->old()->create();

        $response = $this->get(route('admin.reports.clients.index', ['period' => 'month']));

        $response->assertStatus(200);
        $clients = $response->viewData('clients');

        $this->assertCount(1, $clients->items());
        $this->assertEquals($newClient->id, $clients->items()[0]->id);
    }

    /** @test */
    public function admin_can_combine_multiple_filters()
    {
        $this->actingAs($this->adminUser);

        $targetClient = ClientUser::factory()
            ->verified()
            ->active()
            ->newThisMonth()
            ->create(['name' => 'João Silva']);

        ClientUser::factory()->unverified()->create(['name' => 'João Santos']);
        ClientUser::factory()->verified()->inactive()->create(['name' => 'João Costa']);

        $response = $this->get(route('admin.reports.clients.index', [
            'search' => 'João',
            'verified' => 'verified',
            'account_status' => 'active',
            'period' => 'month'
        ]));

        $response->assertStatus(200);
        $clients = $response->viewData('clients');

        $this->assertCount(1, $clients->items());
        $this->assertEquals($targetClient->id, $clients->items()[0]->id);
    }

    /** @test */
    public function admin_can_view_client_details()
    {
        $this->actingAs($this->adminUser);

        $client = ClientUser::factory()->create(['name' => 'João Silva']);
        $address = Address::factory()->forUser($client)->default()->create();

        $response = $this->get(route('admin.reports.clients.show', $client->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.clients.show');
        $response->assertViewHas(['client', 'clientStats']);

        $viewClient = $response->viewData('client');
        $this->assertEquals($client->id, $viewClient->id);
    }

    /** @test */
    public function admin_gets_404_for_nonexistent_client()
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.reports.clients.show', 'nonexistent-id'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_can_export_clients_to_csv()
    {
        $this->actingAs($this->adminUser);

        ClientUser::factory()->count(5)->create();

        $response = $this->get(route('admin.reports.clients.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        $content = $response->getContent();
        $this->assertStringContainsString('Nome', $content); // CSV header
        $this->assertStringContainsString('Email', $content); // CSV header
    }

    /** @test */
    public function admin_can_export_filtered_clients()
    {
        $this->actingAs($this->adminUser);

        $verifiedClient = ClientUser::factory()->verified()->create(['name' => 'João Silva']);
        $unverifiedClient = ClientUser::factory()->unverified()->create(['name' => 'Maria Santos']);

        $response = $this->get(route('admin.reports.clients.export', ['verified' => 'verified']));

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('João Silva', $content);
        $this->assertStringNotContainsString('Maria Santos', $content);
    }

    /** @test */
    public function admin_can_update_client_status_to_inactive()
    {
        $this->actingAs($this->adminUser);

        $client = ClientUser::factory()->active()->create();

        $response = $this->putJson(route('admin.reports.clients.updateStatus', $client->id), [
            'status' => 'inactive',
            'reason' => 'Policy violation'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'inactive'
        ]);

        $this->assertDatabaseHas('client_users', [
            'id' => $client->id,
            'status' => 'inactive',
            'status_reason' => 'Policy violation'
        ]);
    }

    /** @test */
    public function admin_can_update_client_status_to_active()
    {
        $this->actingAs($this->adminUser);

        $client = ClientUser::factory()->inactive()->create();

        $response = $this->putJson(route('admin.reports.clients.updateStatus', $client->id), [
            'status' => 'active',
            'reason' => 'Account restored'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'active'
        ]);

        $this->assertDatabaseHas('client_users', [
            'id' => $client->id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function admin_cannot_update_status_with_invalid_uuid()
    {
        $this->actingAs($this->adminUser);

        $response = $this->putJson(route('admin.reports.clients.updateStatus', 'invalid-id'), [
            'status' => 'inactive'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false
        ]);
    }

    /** @test */
    public function admin_cannot_update_nonexistent_client_status()
    {
        $this->actingAs($this->adminUser);

        $validUuid = '12345678-1234-1234-1234-123456789012';

        $response = $this->putJson(route('admin.reports.clients.updateStatus', $validUuid), [
            'status' => 'inactive'
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false
        ]);
    }

    /** @test */
    public function admin_cannot_update_to_same_status()
    {
        $this->actingAs($this->adminUser);

        $client = ClientUser::factory()->active()->create();

        $response = $this->putJson(route('admin.reports.clients.updateStatus', $client->id), [
            'status' => 'active'
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false
        ]);
    }

    /** @test */
    public function status_update_logs_audit_trail()
    {
        $this->actingAs($this->adminUser);

        Log::shouldReceive('info')
            ->once()
            ->with('Status do cliente alterado', \Mockery::type('array'));

        $client = ClientUser::factory()->active()->create();

        $response = $this->putJson(route('admin.reports.clients.updateStatus', $client->id), [
            'status' => 'inactive',
            'reason' => 'Test reason'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function client_statistics_are_calculated_correctly()
    {
        $this->actingAs($this->adminUser);

        // Create test data
        $verifiedClients = ClientUser::factory()->verified()->count(8)->create();
        $unverifiedClients = ClientUser::factory()->unverified()->count(2)->create();
        $newClients = ClientUser::factory()->newThisMonth()->count(3)->create();

        $response = $this->get(route('admin.reports.clients.index'));

        $response->assertStatus(200);
        $stats = $response->viewData('stats');

        $this->assertEquals(13, $stats['total_clients']); // 8 + 2 + 3
        $this->assertEquals(3, $stats['new_this_month']);
        $this->assertEquals(11, $stats['verified_clients']); // 8 + 3 (newThisMonth are verified by default)
    }

    /** @test */
    public function pagination_works_correctly_with_filters()
    {
        $this->actingAs($this->adminUser);

        // Create 75 verified clients
        ClientUser::factory()->verified()->count(75)->create();
        // Create 25 unverified clients
        ClientUser::factory()->unverified()->count(25)->create();

        $response = $this->get(route('admin.reports.clients.index', [
            'verified' => 'verified',
            'per_page' => 25
        ]));

        $response->assertStatus(200);
        $clients = $response->viewData('clients');

        $this->assertEquals(25, $clients->perPage());
        $this->assertEquals(75, $clients->total());
        $this->assertEquals(3, $clients->lastPage());
    }

    /** @test */
    public function search_maintains_filters_in_pagination()
    {
        $this->actingAs($this->adminUser);

        // Create clients with specific names
        ClientUser::factory()->verified()->count(30)->create(['name' => 'João Silva']);
        ClientUser::factory()->unverified()->count(20)->create(['name' => 'Maria Santos']);

        $response = $this->get(route('admin.reports.clients.index', [
            'search' => 'João',
            'verified' => 'verified',
            'per_page' => 10
        ]));

        $response->assertStatus(200);
        $clients = $response->viewData('clients');

        $this->assertEquals(10, $clients->perPage());
        $this->assertEquals(30, $clients->total());

        // Check that all items match the search criteria
        foreach ($clients->items() as $client) {
            $this->assertStringContainsString('João', $client->name);
            $this->assertNotNull($client->email_verified_at);
        }
    }
}
