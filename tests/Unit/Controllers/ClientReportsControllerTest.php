<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Admin\ClientReportsController;
use App\Http\Requests\Admin\ClientSearchRequest;
use App\Http\Requests\Admin\ClientStatusUpdateRequest;
use App\Models\ClientUser;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class ClientReportsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ClientReportsController();
        
        // Mock authenticated admin user
        $this->actingAs((object) ['id' => 1, 'email' => 'admin@test.com']);
    }

    /** @test */
    public function index_returns_clients_with_basic_filters()
    {
        // Create test data
        $verifiedClient = ClientUser::factory()->verified()->create(['name' => 'João Silva']);
        $unverifiedClient = ClientUser::factory()->unverified()->create(['name' => 'Maria Santos']);

        // Mock request
        $request = Mockery::mock(ClientSearchRequest::class);
        $request->shouldReceive('filled')->with('search')->andReturn(false);
        $request->shouldReceive('filled')->with('verified')->andReturn(false);
        $request->shouldReceive('filled')->with('account_status')->andReturn(false);
        $request->shouldReceive('filled')->with('period')->andReturn(false);
        $request->shouldReceive('filled')->with('start_date')->andReturn(false);
        $request->shouldReceive('filled')->with('end_date')->andReturn(false);
        $request->shouldReceive('get')->with('per_page', 50)->andReturn(50);
        $request->shouldReceive('only')->andReturn([]);

        $response = $this->controller->index($request);

        $this->assertEquals('admin.reports.clients.index', $response->getName());
        $this->assertArrayHasKey('clients', $response->getData());
        $this->assertArrayHasKey('stats', $response->getData());
    }

    /** @test */
    public function index_applies_search_filter()
    {
        // Create test data
        $client1 = ClientUser::factory()->create(['name' => 'João Silva']);
        $client2 = ClientUser::factory()->create(['name' => 'Maria Santos']);

        // Mock request with search
        $request = Mockery::mock(ClientSearchRequest::class);
        $request->shouldReceive('filled')->with('search')->andReturn(true);
        $request->shouldReceive('get')->with('search')->andReturn('João');
        $request->shouldReceive('filled')->with('verified')->andReturn(false);
        $request->shouldReceive('filled')->with('account_status')->andReturn(false);
        $request->shouldReceive('filled')->with('period')->andReturn(false);
        $request->shouldReceive('filled')->with('start_date')->andReturn(false);
        $request->shouldReceive('filled')->with('end_date')->andReturn(false);
        $request->shouldReceive('get')->with('per_page', 50)->andReturn(50);
        $request->shouldReceive('only')->andReturn(['search' => 'João']);

        $response = $this->controller->index($request);
        $clients = $response->getData()['clients'];

        $this->assertCount(1, $clients->items());
        $this->assertEquals('João Silva', $clients->items()[0]->name);
    }

    /** @test */
    public function index_applies_verification_filter()
    {
        // Create test data
        $verifiedClient = ClientUser::factory()->verified()->create();
        $unverifiedClient = ClientUser::factory()->unverified()->create();

        // Mock request with verification filter
        $request = Mockery::mock(ClientSearchRequest::class);
        $request->shouldReceive('filled')->with('search')->andReturn(false);
        $request->shouldReceive('filled')->with('verified')->andReturn(true);
        $request->shouldReceive('get')->with('verified')->andReturn('verified');
        $request->shouldReceive('filled')->with('account_status')->andReturn(false);
        $request->shouldReceive('filled')->with('period')->andReturn(false);
        $request->shouldReceive('filled')->with('start_date')->andReturn(false);
        $request->shouldReceive('filled')->with('end_date')->andReturn(false);
        $request->shouldReceive('get')->with('per_page', 50)->andReturn(50);
        $request->shouldReceive('only')->andReturn(['verified' => 'verified']);

        $response = $this->controller->index($request);
        $clients = $response->getData()['clients'];

        $this->assertCount(1, $clients->items());
        $this->assertNotNull($clients->items()[0]->email_verified_at);
    }

    /** @test */
    public function index_applies_status_filter()
    {
        // Create test data
        $activeClient = ClientUser::factory()->active()->create();
        $inactiveClient = ClientUser::factory()->inactive()->create();

        // Mock request with status filter
        $request = Mockery::mock(ClientSearchRequest::class);
        $request->shouldReceive('filled')->with('search')->andReturn(false);
        $request->shouldReceive('filled')->with('verified')->andReturn(false);
        $request->shouldReceive('filled')->with('account_status')->andReturn(true);
        $request->shouldReceive('get')->with('account_status')->andReturn('active');
        $request->shouldReceive('filled')->with('period')->andReturn(false);
        $request->shouldReceive('filled')->with('start_date')->andReturn(false);
        $request->shouldReceive('filled')->with('end_date')->andReturn(false);
        $request->shouldReceive('get')->with('per_page', 50)->andReturn(50);
        $request->shouldReceive('only')->andReturn(['account_status' => 'active']);

        $response = $this->controller->index($request);
        $clients = $response->getData()['clients'];

        $this->assertCount(1, $clients->items());
        $this->assertEquals('active', $clients->items()[0]->status);
    }

    /** @test */
    public function index_applies_period_filter()
    {
        // Create test data
        $newClient = ClientUser::factory()->newThisMonth()->create();
        $oldClient = ClientUser::factory()->old()->create();

        // Mock request with period filter
        $request = Mockery::mock(ClientSearchRequest::class);
        $request->shouldReceive('filled')->with('search')->andReturn(false);
        $request->shouldReceive('filled')->with('verified')->andReturn(false);
        $request->shouldReceive('filled')->with('account_status')->andReturn(false);
        $request->shouldReceive('filled')->with('period')->andReturn(true);
        $request->shouldReceive('get')->with('period')->andReturn('month');
        $request->shouldReceive('filled')->with('start_date')->andReturn(false);
        $request->shouldReceive('filled')->with('end_date')->andReturn(false);
        $request->shouldReceive('get')->with('per_page', 50)->andReturn(50);
        $request->shouldReceive('only')->andReturn(['period' => 'month']);

        $response = $this->controller->index($request);
        $clients = $response->getData()['clients'];

        $this->assertCount(1, $clients->items());
        $this->assertEquals($newClient->id, $clients->items()[0]->id);
    }

    /** @test */
    public function show_returns_client_details()
    {
        $client = ClientUser::factory()->create();
        $address = Address::factory()->forUser($client)->create();

        $request = new Request();
        $response = $this->controller->show($request, $client->id);

        $this->assertEquals('admin.reports.clients.show', $response->getName());
        $this->assertArrayHasKey('client', $response->getData());
        $this->assertArrayHasKey('clientStats', $response->getData());
        $this->assertEquals($client->id, $response->getData()['client']->id);
    }

    /** @test */
    public function show_returns_404_for_nonexistent_client()
    {
        $request = new Request();
        $response = $this->controller->show($request, 'nonexistent-id');

        $this->assertEquals(302, $response->getStatusCode()); // Redirect
    }

    /** @test */
    public function export_generates_csv_response()
    {
        // Create test data
        $client = ClientUser::factory()->create(['name' => 'João Silva']);
        Address::factory()->forUser($client)->default()->create();

        // Mock request
        $request = Mockery::mock(ClientSearchRequest::class);
        $request->shouldReceive('filled')->andReturn(false);
        $request->shouldReceive('get')->andReturn(null);
        $request->shouldReceive('only')->andReturn([]);

        $response = $this->controller->export($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function update_status_successfully_updates_client_status()
    {
        $client = ClientUser::factory()->active()->create();

        // Mock request
        $request = Mockery::mock(ClientStatusUpdateRequest::class);
        $request->shouldReceive('get')->with('status')->andReturn('inactive');
        $request->shouldReceive('get')->with('reason')->andReturn('Test reason');
        $request->shouldReceive('ip')->andReturn('127.0.0.1');
        $request->shouldReceive('userAgent')->andReturn('Test Agent');

        $response = $this->controller->updateStatus($request, $client->id);

        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('inactive', $responseData['status']);
        
        // Verify database was updated
        $this->assertEquals('inactive', $client->fresh()->status);
    }

    /** @test */
    public function update_status_returns_error_for_invalid_uuid()
    {
        $request = Mockery::mock(ClientStatusUpdateRequest::class);
        $request->shouldReceive('ip')->andReturn('127.0.0.1');

        $response = $this->controller->updateStatus($request, 'invalid-id');

        $this->assertEquals(400, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertStringContainsString('inválido', $responseData['message']);
    }

    /** @test */
    public function update_status_returns_error_for_nonexistent_client()
    {
        $validUuid = '12345678-1234-1234-1234-123456789012';
        
        $request = Mockery::mock(ClientStatusUpdateRequest::class);
        $request->shouldReceive('ip')->andReturn('127.0.0.1');
        $request->shouldReceive('userAgent')->andReturn('Test Agent');

        $response = $this->controller->updateStatus($request, $validUuid);

        $this->assertEquals(404, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertStringContainsString('não encontrado', $responseData['message']);
    }

    /** @test */
    public function update_status_returns_error_for_same_status()
    {
        $client = ClientUser::factory()->active()->create();

        $request = Mockery::mock(ClientStatusUpdateRequest::class);
        $request->shouldReceive('get')->with('status')->andReturn('active');
        $request->shouldReceive('ip')->andReturn('127.0.0.1');

        $response = $this->controller->updateStatus($request, $client->id);

        $this->assertEquals(422, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertStringContainsString('já possui este status', $responseData['message']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}