<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StatusEndpointTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_status_endpoint_returns_correct_structure(): void
    {
        $response = $this->getJson('/api/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'date',
                    'upload_max_filesize',
                    'post_max_size',
                    'php_ini_path',
                    'php_version',
                    'laravel_version',
                ],
            ])
            ->assertJson([
                'status' => 200,
                'message' => 'Backend running',
            ]);
    }
}
