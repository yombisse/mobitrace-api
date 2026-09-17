<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiFoundationTest extends TestCase
{
    public function test_api_health_uses_the_standard_success_envelope(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonStructure(['success', 'message', 'data'])
            ->assertJson([
                'success' => true,
                'data' => ['status' => 'ok'],
                'message' => 'API opérationnelle.',
            ]);
    }

    public function test_protected_api_route_returns_the_standard_unauthenticated_error(): void
    {
        $this->getJson('/api/v1/health/authenticated')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_unknown_api_route_returns_the_standard_not_found_error(): void
    {
        $this->getJson('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Ressource introuvable.',
            ]);
    }

    public function test_validation_errors_use_the_standard_error_envelope(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['success', 'message', 'errors'])
            ->assertJson([
                'success' => false,
                'message' => 'Les données fournies sont invalides.',
            ]);
    }
}
