<?php

namespace Tests\Feature;

use App\Models\Code;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can get account info with codes.
     */
    public function test_authenticated_user_can_get_account_with_codes(): void
    {
        $country = Country::factory()->create();

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'country_id' => $country->id,
            'id_type' => 'Passport',
            'id_number' => '123456789',
            'phone_number' => '12345678',
            'data_treatment_accepted' => true,
            'terms_accepted' => true,
        ]);

        // Create some codes for the user
        Code::create(['user_id' => $user->id, 'code' => 'ABC123']);
        Code::create(['user_id' => $user->id, 'code' => 'XYZ789']);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/protected/account');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'name',
                    'codes' => [
                        '*' => [
                            'id',
                            'user_id',
                            'code',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'status' => 200,
                'message' => 'Account retrieved successfully',
                'data' => [
                    'name' => 'John Doe',
                ],
            ]);

        $this->assertCount(2, $response->json('data.codes'));
    }

    /**
     * Test unauthenticated user cannot access account.
     */
    public function test_unauthenticated_user_cannot_access_account(): void
    {
        $response = $this->getJson('/api/protected/account');

        $response->assertStatus(401);
    }
}
