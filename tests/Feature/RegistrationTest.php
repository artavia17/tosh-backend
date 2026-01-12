<?php

namespace Tests\Feature;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful registration.
     */
    public function test_user_can_register_successfully(): void
    {
        $country = Country::factory()->create();

        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'country_id' => $country->id,
            'id_type' => 'Passport',
            'id_number' => '123456789',
            'phone_number' => '12345678',
            'marketing_opt_in' => true,
            'whatsapp_opt_in' => false,
            'phone_opt_in' => false,
            'email_opt_in' => true,
            'sms_opt_in' => false,
            'data_treatment_accepted' => true,
            'terms_accepted' => true,
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'country_id',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'country_id' => $country->id,
        ]);
    }

    /**
     * Test validation errors.
     */
    public function test_registration_requires_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'country_id',
                'id_type',
                'id_number',
                'phone_number',
                'data_treatment_accepted',
                'terms_accepted',
            ]);
    }
}
