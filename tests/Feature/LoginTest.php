<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login.
     */
    public function test_user_can_login_with_id_number(): void
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

        $response = $this->postJson('/api/auth/sign-in', [
            'id_number' => '123456789',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user',
                    'token',
                ],
            ])
            ->assertJson([
                'status' => 200,
                'message' => 'Login successful',
            ]);
    }

    /**
     * Test login with invalid ID number.
     */
    public function test_login_fails_with_invalid_id_number(): void
    {
        $response = $this->postJson('/api/auth/sign-in', [
            'id_number' => 'nonexistent',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => 'User not found',
            ]);
    }

    /**
     * Test login requires ID number.
     */
    public function test_login_requires_id_number(): void
    {
        $response = $this->postJson('/api/auth/sign-in', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_number']);
    }
}
