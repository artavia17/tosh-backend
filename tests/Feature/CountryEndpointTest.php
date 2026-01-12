<?php

namespace Tests\Feature;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CountryEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_countries_endpoint_returns_correct_structure(): void
    {
        // Seed the database using the seeder to get all countries
        $this->seed(\Database\Seeders\CountrySeeder::class);

        $response = $this->getJson('/api/countries');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'iso_code',
                        'phone_code',
                        'id_format',
                        'phone_format',
                        'phone_min_length',
                        'phone_max_length',
                        'id_min_length',
                        'id_max_length',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'name' => 'Costa Rica',
                'iso_code' => 'CR',
                'phone_code' => '506',
            ])
            ->assertJsonFragment([
                'name' => 'México',
                'iso_code' => 'MX',
                'phone_code' => '52',
            ]);

        $this->assertCount(6, $response->json('data'));
    }
}
