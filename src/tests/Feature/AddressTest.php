<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Create a user a address.
     *
     * @return void
     */
    public function test_create_user_address()
    {
        $student = $this->createUser();
        $token = $this->accessToken($student);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->json('POST', '/api/addresses', [
                'postcode' => $this->faker->postcode(),
                'street' => $this->faker->streetAddress(),
                'number' => $this->faker->word,
                'complement' => $this->faker->word(),
                'state' => $this->faker->word(),
                'city' => $this->faker->city(),
                'country' => 'BRA',
            ]);
        $response->assertStatus(201);
    }
}
