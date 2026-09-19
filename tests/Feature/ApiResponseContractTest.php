<?php

namespace Tests\Feature;

use App\Models\Reseau;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiResponseContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_accepts_an_agent_without_an_email(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Agent Mobile',
            'telephone' => '70000003',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', null);
    }

    public function test_login_and_logout_use_the_standard_envelope(): void
    {
        $user = User::factory()->create([
            'telephone' => '70000001',
            'password' => 'password',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'telephone' => '70000001',
            'password' => 'password',
        ]);

        $login->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user'],
            ])
            ->assertJsonPath('success', true);

        $this->withToken($login->json('data.token'))
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Déconnexion réussie.',
            ]);
    }

    public function test_transaction_workflow_and_read_endpoints_use_the_standard_envelope(): void
    {
        $user = User::factory()->create();
        $reseau = Reseau::factory()->create();

        $created = $this->actingAs($user)->postJson('/api/v1/transactions', [
            'telephone' => '70000002',
            'nom' => 'Doe',
            'prenoms' => 'Jane',
            'reseau_id' => $reseau->id,
            'type_operation' => 'depot',
            'montant' => 70000,
            'solde_apres_operation' => 70000,
            'client_confirme' => true,
        ]);

        $created->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['transaction', 'client', 'client_existant'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.client_existant', false);

        $transactionId = $created->json('data.transaction.id');

        $this->actingAs($user)
            ->getJson('/api/v1/transactions')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1);

        $this->actingAs($user)
            ->getJson('/api/v1/transactions/'.$transactionId)
            ->assertOk()
            ->assertJsonStructure(['success', 'message', 'data'])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $transactionId);

        $this->actingAs($user)
            ->getJson('/api/v1/clients/lookup?telephone=70000002')
            ->assertOk()
            ->assertJsonStructure(['success', 'message', 'data' => ['found', 'client']])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.found', true);

        $this->actingAs($user)
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonStructure(['success', 'message', 'data'])
            ->assertJsonPath('success', true);

        // Test that client_confirme is required
        $this->actingAs($user)
            ->postJson('/api/v1/transactions', [
                'telephone' => '70000003',
                'nom' => 'Smith',
                'prenoms' => 'John',
                'reseau_id' => $reseau->id,
                'type_operation' => 'depot',
                'montant' => 50000,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        // Test that client_confirme must be true
        $this->actingAs($user)
            ->postJson('/api/v1/transactions', [
                'telephone' => '70000004',
                'nom' => 'Johnson',
                'prenoms' => 'Bob',
                'reseau_id' => $reseau->id,
                'type_operation' => 'depot',
                'montant' => 30000,
                'client_confirme' => false,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}