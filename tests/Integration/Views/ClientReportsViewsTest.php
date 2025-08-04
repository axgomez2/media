<?php

namespace Tests\Integration\Views;

use App\Models\Address;
use App\Models\ClientUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ClientReportsViewsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function reports_index_view_renders_correctly()
    {
        $stats = [
            'total_clients' => 100,
            'new_this_month' => 15,
            'verified_clients' => 80,
            'verification_rate' => 80.0,
            'active_clients' => 60,
            'clients_with_orders' => 45,
            'conversion_rate' => 45.0,
            'average_value_per_client' => 150.50
        ];

        $view = View::make('admin.reports.index', compact('stats'));
        $rendered = $view->render();

        $this->assertStringContainsString('Relatório de Clientes', $rendered);
        $this->assertStringContainsString('100', $rendered); // Total clients
    }

    /** @test */
    public function clients_index_view_renders_with_clients_data()
    {
        // Create test data
        $clients = collect([
            ClientUser::factory()->verified()->create(['name' => 'João Silva']),
            ClientUser::factory()->unverified()->create(['name' => 'Maria Santos']),
        ]);

        // Mock paginated collection
        $paginatedClients = new \Illuminate\Pagination\LengthAwarePaginator(
            $clients,
            $clients->count(),
            50,
            1,
            ['path' => request()->url()]
        );

        $stats = [
            'total_clients' => 2,
            'new_this_month' => 1,
            'verified_clients' => 1,
            'verification_rate' => 50.0,
            'active_clients' => 2,
            'clients_with_orders' => 0,
            'conversion_rate' => 0.0,
            'average_value_per_client' => 0.0
        ];

        $view = View::make('admin.reports.clients.index', [
            'clients' => $paginatedClients,
            'stats' => $stats
        ]);
        $rendered = $view->render();

        $this->assertStringContainsString('João Silva', $rendered);
        $this->assertStringContainsString('Maria Santos', $rendered);
        $this->assertStringContainsString('Buscar clientes', $rendered);
        $this->assertStringContainsString('Exportar CSV', $rendered);
    }

    /** @test */
    public function clients_index_view_displays_search_filters()
    {
        $clients = new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            50,
            1,
            ['path' => request()->url()]
        );

        $stats = [
            'total_clients' => 0,
            'new_this_month' => 0,
            'verified_clients' => 0,
            'verification_rate' => 0,
            'active_clients' => 0,
            'clients_with_orders' => 0,
            'conversion_rate' => 0,
            'average_value_per_client' => 0
        ];

        $view = View::make('admin.reports.clients.index', [
            'clients' => $clients,
            'stats' => $stats
        ]);
        $rendered = $view->render();

        $this->assertStringContainsString('name="search"', $rendered);
        $this->assertStringContainsString('name="verified"', $rendered);
        $this->assertStringContainsString('name="account_status"', $rendered);
        $this->assertStringContainsString('name="period"', $rendered);
    }

    /** @test */
    public function clients_index_view_displays_statistics_cards()
    {
        $clients = new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            50,
            1,
            ['path' => request()->url()]
        );

        $stats = [
            'total_clients' => 150,
            'new_this_month' => 25,
            'verified_clients' => 120,
            'verification_rate' => 80.0,
            'active_clients' => 90,
            'clients_with_orders' => 75,
            'conversion_rate' => 50.0,
            'average_value_per_client' => 200.75
        ];

        $view = View::make('admin.reports.clients.index', [
            'clients' => $clients,
            'stats' => $stats
        ]);
        $rendered = $view->render();

        $this->assertStringContainsString('150', $rendered); // Total clients
        $this->assertStringContainsString('25', $rendered);  // New this month
        $this->assertStringContainsString('80%', $rendered); // Verification rate
        $this->assertStringContainsString('50%', $rendered); // Conversion rate
    }

    /** @test */
    public function clients_show_view_renders_client_details()
    {
        $client = ClientUser::factory()->verified()->create([
            'name' => 'João Silva Santos',
            'email' => 'joao@example.com'
        ]);

        $defaultAddress = Address::factory()->forUser($client)->default()->create([
            'street' => 'Rua das Flores',
            'number' => '123',
            'city' => 'São Paulo',
            'state' => 'SP'
        ]);

        $clientStats = [
            'total_orders' => 5,
            'total_spent' => 500.00,
            'average_order_value' => 100.00,
            'last_order_date' => now()->subDays(10),
            'wishlist_items' => 3,
            'cart_items' => 2,
            'cart_total' => 150.00,
            'has_abandoned_cart' => false,
            'registration_days' => 30,
            'last_activity' => now()->subDays(2)
        ];

        $view = View::make('admin.reports.clients.show', [
            'client' => $client,
            'clientStats' => $clientStats
        ]);
        $rendered = $view->render();

        $this->assertStringContainsString('João Silva Santos', $rendered);
        $this->assertStringContainsString('joao@example.com', $rendered);
        $this->assertStringContainsString('Rua das Flores', $rendered);
        $this->assertStringContainsString('São Paulo', $rendered);
    }

    /** @test */
    public function clients_show_view_displays_client_statistics()
    {
        $client = ClientUser::factory()->create();

        $clientStats = [
            'total_orders' => 10,
            'total_spent' => 1500.50,
            'average_order_value' => 150.05,
            'last_order_date' => now()->subDays(5),
            'wishlist_items' => 8,
            'cart_items' => 3,
            'cart_total' => 299.99,
            'has_abandoned_cart' => true,
            'registration_days' => 90,
            'last_activity' => now()->subDays(1)
        ];

        $view = View::make('admin.reports.clients.show', [
            'client' => $client,
            'clientStats' => $clientStats
        ]);
        $rendered = $view->render();

        $this->assertStringContainsString('10', $rendered);      // Total orders
        $this->assertStringContainsString('1.500,50', $rendered); // Total spent (formatted)
        $this->assertStringContainsString('150,05', $rendered);   // Average order value
        $this->assertStringContainsString('8', $rendered);       // Wishlist items
        $this->assertStringContainsString('3', $rendered);       // Cart items
    }

    /** @test */
    public function clients_show_view_displays_addresses_section()
    {
        $client = ClientUser::factory()->create();
        
        $defaultAddress = Address::factory()->forUser($client)->default()->create([
            'street' => 'Rua Principal',
            'number' => '100',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ'
        ]);

        $secondaryAddress = Address::factory()->forUser($client)->create([
            'street' => 'Rua Secundária',
            'number' => '200',
            'city' => 'São Paulo',
            'state' => 'SP'
        ]);

        $clientStats = [
            'total_orders' => 0,
            'total_spent' => 0,
            'average_order_value' => 0,
            'last_order_date' => null,
            'wishlist_items' => 0,
            'cart_items' => 0,
            'cart_total' => 0,
            'has_abandoned_cart' => false,
            'registration_days' => 1,
            'last_activity' => now()
        ];

        $view = View::make('admin.reports.clients.show', [
            'client' => $client->load('addresses'),
            'clientStats' => $clientStats
        ]);
        $rendered = $view->render();

        $this->assertStringContainsString('Endereços', $rendered);
        $this->assertStringContainsString('Rua Principal', $rendered);
        $this->assertStringContainsString('Rio de Janeiro', $rendered);
        $this->assertStringContainsString('Padrão', $rendered); // Default address indicator
    }

    /** @test */
    public function clients_show_view_handles_client_without_addresses()
    {
        $client = ClientUser::factory()->create();

        $clientStats = [
            'total_orders' => 0,
            'total_spent' => 0,
            'average_order_value' => 0,
            'last_order_date' => null,
            'wishlist_items' => 0,
            'cart_items' => 0,
            'cart_total' => 0,
            'has_abandoned_cart' => false,
            'registration_days' => 1,
            'last_activity' => now()
        ];

        $view = View::make('admin.reports.clients.show', [
            'client' => $client,
            'clientStats' => $clientStats
        ]);
        $rendered = $view->render();

        $this->assertStringContainsString('Nenhum endereço cadastrado', $rendered);
    }

    /** @test */
    public function clients_show_view_displays_status_badges()
    {
        $activeClient = ClientUser::factory()->active()->verified()->create();
        $inactiveClient = ClientUser::factory()->inactive()->unverified()->create();

        $clientStats = [
            'total_orders' => 0,
            'total_spent' => 0,
            'average_order_value' => 0,
            'last_order_date' => null,
            'wishlist_items' => 0,
            'cart_items' => 0,
            'cart_total' => 0,
            'has_abandoned_cart' => false,
            'registration_days' => 1,
            'last_activity' => now()
        ];

        // Test active client
        $view = View::make('admin.reports.clients.show', [
            'client' => $activeClient,
            'clientStats' => $clientStats
        ]);
        $rendered = $view->render();

        $this->assertStringContainsString('Ativo', $rendered);
        $this->assertStringContainsString('Verificado', $rendered);

        // Test inactive client
        $view = View::make('admin.reports.clients.show', [
            'client' => $inactiveClient,
            'clientStats' => $clientStats
        ]);
        $rendered = $view->render();

        $this->assertStringContainsString('Inativo', $rendered);
        $this->assertStringContainsString('Não verificado', $rendered);
    }

    /** @test */
    public function clients_show_view_displays_action_buttons()
    {
        $client = ClientUser::factory()->active()->create();

        $clientStats = [
            'total_orders' => 0,
            'total_spent' => 0,
            'average_order_value' => 0,
            'last_order_date' => null,
            'wishlist_items' => 0,
            'cart_items' => 0,
            'cart_total' => 0,
            'has_abandoned_cart' => false,
            'registration_days' => 1,
            'last_activity' => now()
        ];

        $view = View::make('admin.reports.clients.show', [
            'client' => $client,
            'clientStats' => $clientStats
        ]);
        $rendered = $view->render();

        $this->assertStringContainsString('Desativar', $rendered); // Status toggle button
        $this->assertStringContainsString('Voltar', $rendered);    // Back button
    }
}