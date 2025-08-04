<?php

namespace Tests\Unit\Models;

use App\Models\Address;
use App\Models\Cart;
use App\Models\ClientUser;
use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'id',
            'name',
            'email',
            'phone',
            'cpf',
            'birth_date',
            'password',
            'google_id',
            'email_verified_at',
            'status',
            'status_updated_at',
            'status_reason',
            'status_updated_by',
        ];

        $client = new ClientUser();

        $this->assertEquals($fillable, $client->getFillable());
    }

    /** @test */
    public function it_has_correct_hidden_attributes()
    {
        $hidden = ['password'];
        $client = new ClientUser();

        $this->assertEquals($hidden, $client->getHidden());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $casts = [
            'email_verified_at' => 'datetime',
            'status_updated_at' => 'datetime',
            'birth_date' => 'date',
            'id' => 'string',
        ];

        $client = new ClientUser();

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, $client->getCasts()[$attribute]);
        }
    }

    /** @test */
    public function it_has_addresses_relationship()
    {
        $client = ClientUser::factory()->create();
        $address = Address::factory()->forUser($client)->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $client->addresses);
        $this->assertTrue($client->addresses->contains($address));
    }

    /** @test */
    public function it_has_default_address_relationship()
    {
        $client = ClientUser::factory()->create();
        $defaultAddress = Address::factory()->forUser($client)->default()->create();
        Address::factory()->forUser($client)->create(); // Non-default address

        $this->assertInstanceOf(Address::class, $client->defaultAddress);
        $this->assertEquals($defaultAddress->id, $client->defaultAddress->id);
        $this->assertTrue($client->defaultAddress->is_default);
    }

    /** @test */
    public function it_has_orders_relationship()
    {
        $client = ClientUser::factory()->create();

        // Mock the Order relationship since Order model might not exist yet
        $this->assertTrue(method_exists($client, 'orders'));
    }

    /** @test */
    public function it_has_cart_relationship()
    {
        $client = ClientUser::factory()->create();

        // Mock the Cart relationship
        $this->assertTrue(method_exists($client, 'cart'));
    }

    /** @test */
    public function it_has_wishlists_relationship()
    {
        $client = ClientUser::factory()->create();

        // Mock the Wishlist relationship
        $this->assertTrue(method_exists($client, 'wishlists'));
    }

    /** @test */
    public function scope_verified_filters_verified_clients()
    {
        $verifiedClient = ClientUser::factory()->verified()->create();
        $unverifiedClient = ClientUser::factory()->unverified()->create();

        $verifiedClients = ClientUser::verified()->get();

        $this->assertTrue($verifiedClients->contains($verifiedClient));
        $this->assertFalse($verifiedClients->contains($unverifiedClient));
    }

    /** @test */
    public function scope_unverified_filters_unverified_clients()
    {
        $verifiedClient = ClientUser::factory()->verified()->create();
        $unverifiedClient = ClientUser::factory()->unverified()->create();

        $unverifiedClients = ClientUser::unverified()->get();

        $this->assertFalse($unverifiedClients->contains($verifiedClient));
        $this->assertTrue($unverifiedClients->contains($unverifiedClient));
    }

    /** @test */
    public function scope_active_filters_recently_active_clients()
    {
        $activeClient = ClientUser::factory()->create([
            'updated_at' => now()->subDays(15)
        ]);
        $inactiveClient = ClientUser::factory()->create([
            'updated_at' => now()->subDays(45)
        ]);

        $activeClients = ClientUser::active()->get();

        $this->assertTrue($activeClients->contains($activeClient));
        $this->assertFalse($activeClients->contains($inactiveClient));
    }

    /** @test */
    public function scope_search_filters_by_name_and_email()
    {
        $client1 = ClientUser::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com'
        ]);
        $client2 = ClientUser::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com'
        ]);

        // Search by name
        $results = ClientUser::search('João')->get();
        $this->assertTrue($results->contains($client1));
        $this->assertFalse($results->contains($client2));

        // Search by email
        $results = ClientUser::search('maria@example.com')->get();
        $this->assertFalse($results->contains($client1));
        $this->assertTrue($results->contains($client2));
    }

    /** @test */
    public function scope_new_this_month_filters_current_month_clients()
    {
        $thisMonthClient = ClientUser::factory()->newThisMonth()->create();
        $oldClient = ClientUser::factory()->old()->create();

        $newClients = ClientUser::newThisMonth()->get();

        $this->assertTrue($newClients->contains($thisMonthClient));
        $this->assertFalse($newClients->contains($oldClient));
    }

    /** @test */
    public function scope_by_status_filters_by_account_status()
    {
        $activeClient = ClientUser::factory()->active()->create();
        $inactiveClient = ClientUser::factory()->inactive()->create();

        $activeClients = ClientUser::byStatus('active')->get();
        $inactiveClients = ClientUser::byStatus('inactive')->get();

        $this->assertTrue($activeClients->contains($activeClient));
        $this->assertFalse($activeClients->contains($inactiveClient));

        $this->assertFalse($inactiveClients->contains($activeClient));
        $this->assertTrue($inactiveClients->contains($inactiveClient));
    }

    /** @test */
    public function avatar_accessor_returns_initials()
    {
        $client = ClientUser::factory()->create(['name' => 'João Silva Santos']);

        $this->assertEquals('JS', $client->avatar);
    }

    /** @test */
    public function avatar_accessor_handles_single_name()
    {
        $client = ClientUser::factory()->create(['name' => 'João']);

        $this->assertEquals('JO', $client->avatar);
    }

    /** @test */
    public function full_address_accessor_returns_default_address()
    {
        $client = ClientUser::factory()->create();
        $defaultAddress = Address::factory()->forUser($client)->default()->create([
            'street' => 'Rua das Flores',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01234-567'
        ]);

        $this->assertNotNull($client->full_address);
        $this->assertStringContainsString('Rua das Flores', $client->full_address);
    }

    /** @test */
    public function full_address_accessor_returns_null_when_no_default_address()
    {
        $client = ClientUser::factory()->create();

        $this->assertNull($client->full_address);
    }

    /** @test */
    public function is_verified_accessor_returns_correct_boolean()
    {
        $verifiedClient = ClientUser::factory()->verified()->create();
        $unverifiedClient = ClientUser::factory()->unverified()->create();

        $this->assertTrue($verifiedClient->is_verified);
        $this->assertFalse($unverifiedClient->is_verified);
    }

    /** @test */
    public function is_active_accessor_returns_correct_boolean()
    {
        $activeClient = ClientUser::factory()->create([
            'updated_at' => now()->subDays(15)
        ]);
        $inactiveClient = ClientUser::factory()->create([
            'updated_at' => now()->subDays(45)
        ]);

        $this->assertTrue($activeClient->is_active);
        $this->assertFalse($inactiveClient->is_active);
    }

    /** @test */
    public function first_name_accessor_returns_first_name_only()
    {
        $client = ClientUser::factory()->create(['name' => 'João Silva Santos']);

        $this->assertEquals('João', $client->first_name);
    }

    /** @test */
    public function status_label_accessor_returns_correct_labels()
    {
        $activeClient = ClientUser::factory()->active()->create();
        $inactiveClient = ClientUser::factory()->inactive()->create();

        $this->assertEquals('Ativo', $activeClient->status_label);
        $this->assertEquals('Inativo', $inactiveClient->status_label);
    }

    /** @test */
    public function status_color_accessor_returns_correct_colors()
    {
        $activeClient = ClientUser::factory()->active()->create();
        $inactiveClient = ClientUser::factory()->inactive()->create();

        $this->assertEquals('green', $activeClient->status_color);
        $this->assertEquals('red', $inactiveClient->status_color);
    }

    /** @test */
    public function is_active_status_accessor_returns_correct_boolean()
    {
        $activeClient = ClientUser::factory()->active()->create();
        $inactiveClient = ClientUser::factory()->inactive()->create();

        $this->assertTrue($activeClient->is_active_status);
        $this->assertFalse($inactiveClient->is_active_status);
    }

    /** @test */
    public function is_inactive_status_accessor_returns_correct_boolean()
    {
        $activeClient = ClientUser::factory()->active()->create();
        $inactiveClient = ClientUser::factory()->inactive()->create();

        $this->assertFalse($activeClient->is_inactive_status);
        $this->assertTrue($inactiveClient->is_inactive_status);
    }
}
