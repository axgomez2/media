<?php

namespace Tests\Unit\Models;

use App\Models\Address;
use App\Models\ClientUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'street',
            'number',
            'complement',
            'neighborhood',
            'city',
            'state',
            'zip_code',
            'is_default'
        ];

        $address = new Address();

        $this->assertEquals($fillable, $address->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $casts = [
            'is_default' => 'boolean',
            'user_id' => 'string'
        ];

        $address = new Address();

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, $address->getCasts()[$attribute]);
        }
    }

    /** @test */
    public function it_belongs_to_client_user()
    {
        $client = ClientUser::factory()->create();
        $address = Address::factory()->forUser($client)->create();

        $this->assertInstanceOf(ClientUser::class, $address->user);
        $this->assertEquals($client->id, $address->user->id);
    }

    /** @test */
    public function full_address_accessor_formats_address_correctly()
    {
        $address = Address::factory()->create([
            'street' => 'Rua das Flores',
            'number' => '123',
            'complement' => 'Apto 45',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01234567'
        ]);

        $expected = 'Rua das Flores, 123, Apto 45, Centro, São Paulo - SP, CEP: 01234567';
        $this->assertEquals($expected, $address->full_address);
    }

    /** @test */
    public function full_address_accessor_handles_no_complement()
    {
        $address = Address::factory()->create([
            'street' => 'Rua das Flores',
            'number' => '123',
            'complement' => null,
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01234567'
        ]);

        $expected = 'Rua das Flores, 123, Centro, São Paulo - SP, CEP: 01234567';
        $this->assertEquals($expected, $address->full_address);
    }

    /** @test */
    public function formatted_zip_code_accessor_formats_8_digit_zip_code()
    {
        $address = Address::factory()->create(['zip_code' => '01234567']);

        $this->assertEquals('01234-567', $address->formatted_zip_code);
    }

    /** @test */
    public function formatted_zip_code_accessor_handles_already_formatted_zip_code()
    {
        $address = Address::factory()->create(['zip_code' => '01234-567']);

        $this->assertEquals('01234-567', $address->formatted_zip_code);
    }

    /** @test */
    public function formatted_zip_code_accessor_handles_invalid_zip_code()
    {
        $address = Address::factory()->create(['zip_code' => '123']);

        $this->assertEquals('123', $address->formatted_zip_code);
    }

    /** @test */
    public function short_address_accessor_returns_short_format()
    {
        $address = Address::factory()->create([
            'street' => 'Rua das Flores',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo'
        ]);

        $expected = 'Rua das Flores, 123, Centro, São Paulo';
        $this->assertEquals($expected, $address->short_address);
    }

    /** @test */
    public function scope_default_filters_default_addresses()
    {
        $client = ClientUser::factory()->create();
        $defaultAddress = Address::factory()->forUser($client)->default()->create();
        $regularAddress = Address::factory()->forUser($client)->create();

        $defaultAddresses = Address::default()->get();

        $this->assertTrue($defaultAddresses->contains($defaultAddress));
        $this->assertFalse($defaultAddresses->contains($regularAddress));
    }

    /** @test */
    public function scope_by_city_filters_by_city()
    {
        $spAddress = Address::factory()->inCity('São Paulo')->create();
        $rjAddress = Address::factory()->inCity('Rio de Janeiro')->create();

        $spAddresses = Address::byCity('São Paulo')->get();
        $rjAddresses = Address::byCity('Rio')->get(); // Partial match

        $this->assertTrue($spAddresses->contains($spAddress));
        $this->assertFalse($spAddresses->contains($rjAddress));

        $this->assertTrue($rjAddresses->contains($rjAddress));
        $this->assertFalse($rjAddresses->contains($spAddress));
    }

    /** @test */
    public function scope_by_state_filters_by_state()
    {
        $spAddress = Address::factory()->inState('SP')->create();
        $rjAddress = Address::factory()->inState('RJ')->create();

        $spAddresses = Address::byState('SP')->get();

        $this->assertTrue($spAddresses->contains($spAddress));
        $this->assertFalse($spAddresses->contains($rjAddress));
    }

    /** @test */
    public function scope_by_zip_code_filters_by_zip_code()
    {
        $address1 = Address::factory()->withZipCode('01234567')->create();
        $address2 = Address::factory()->withZipCode('98765432')->create();

        $results = Address::byZipCode('01234')->get();

        $this->assertTrue($results->contains($address1));
        $this->assertFalse($results->contains($address2));
    }

    /** @test */
    public function scope_by_zip_code_handles_formatted_zip_code()
    {
        $address = Address::factory()->withZipCode('01234567')->create();

        $results = Address::byZipCode('01234-567')->get();

        $this->assertTrue($results->contains($address));
    }

    /** @test */
    public function set_as_default_method_sets_address_as_default()
    {
        $client = ClientUser::factory()->create();
        $address1 = Address::factory()->forUser($client)->default()->create();
        $address2 = Address::factory()->forUser($client)->create();

        // Initially address1 is default
        $this->assertTrue($address1->fresh()->is_default);
        $this->assertFalse($address2->fresh()->is_default);

        // Set address2 as default
        $address2->setAsDefault();

        // Now address2 should be default and address1 should not
        $this->assertFalse($address1->fresh()->is_default);
        $this->assertTrue($address2->fresh()->is_default);
    }

    /** @test */
    public function set_as_default_method_only_affects_same_user_addresses()
    {
        $client1 = ClientUser::factory()->create();
        $client2 = ClientUser::factory()->create();

        $address1 = Address::factory()->forUser($client1)->default()->create();
        $address2 = Address::factory()->forUser($client2)->default()->create();
        $address3 = Address::factory()->forUser($client1)->create();

        // Set address3 as default for client1
        $address3->setAsDefault();

        // address1 should no longer be default, but address2 (different user) should remain default
        $this->assertFalse($address1->fresh()->is_default);
        $this->assertTrue($address2->fresh()->is_default);
        $this->assertTrue($address3->fresh()->is_default);
    }
}
