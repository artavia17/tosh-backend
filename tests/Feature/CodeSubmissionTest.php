<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CodeSubmissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can submit code.
     */
    public function test_authenticated_user_can_submit_code(): void
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

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/protected/codes', [
                    'code' => 'ABC123',
                ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'code',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'status' => 201,
                'message' => 'Code submitted successfully',
            ]);

        $this->assertDatabaseHas('codes', [
            'user_id' => $user->id,
            'code' => 'ABC123',
        ]);
    }

    /**
     * Test unauthenticated user cannot submit code.
     */
    public function test_unauthenticated_user_cannot_submit_code(): void
    {
        $response = $this->postJson('/api/protected/codes', [
            'code' => 'ABC123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test code is required.
     */
    public function test_code_submission_requires_code(): void
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

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/protected/codes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }
}
