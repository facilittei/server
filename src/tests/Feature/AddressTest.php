<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Create a user address.
     *
     * @return void
     */
    public function test_create_user_address()
    {
        $student = $this->createUser();
        $token = $this->accessToken($student);

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
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

    /**
     * Update a user address.
     *
     * @return void
     */
    public function test_update_user_address()
    {
        $student = $this->createUser();
        $token = $this->accessToken($student);

        $createResponse = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->json('POST', '/api/addresses', [
                'postcode' => $this->faker->postcode(),
                'street' => $this->faker->streetAddress(),
                'number' => $this->faker->word,
                'complement' => $this->faker->word(),
                'state' => $this->faker->word(),
                'city' => $this->faker->city(),
                'country' => 'BRA',
            ]);
        $createResponse->assertStatus(201);

        $city = $this->faker->city();
        $createResponse = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->json('PUT', '/api/addresses/'.$createResponse['address']['id'], [
                'city' => $city,
            ]);
        $createResponse->assertStatus(200);
        $this->assertEquals($city, $createResponse['address']['city']);
    }

    /**
     * Destroy a user address.
     *
     * @return void
     */
    public function test_destroy_user_address()
    {
        $student = $this->createUser();
        $token = $this->accessToken($student);

        $createResponse = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->json('POST', '/api/addresses', [
                'postcode' => $this->faker->postcode(),
                'street' => $this->faker->streetAddress(),
                'number' => $this->faker->word,
                'complement' => $this->faker->word(),
                'state' => $this->faker->word(),
                'city' => $this->faker->city(),
                'country' => 'BRA',
            ]);
        $createResponse->assertStatus(201);

        $destroyResponse = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->json('DELETE', '/api/addresses/'.$createResponse['address']['id']);
        $destroyResponse->assertStatus(204);
    }
}
