<?php

namespace Tests\Feature;

use App\Models\Reseau;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReseauAdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_manage_owned_networks_but_not_other_agents_networks_or_grant_admin_role(): void
    {
        $agent = User::factory()->create();
        $otherAgent = User::factory()->create();
        $otherReseau = Reseau::factory()->create(['owner_user_id' => $otherAgent->id]);
        $this->actingAs($agent, 'sanctum');

        $response = $this->postJson('/api/v1/reseaux', [
            'nom' => 'Mon réseau',
            'code' => 'MON-RESEAU',
        ])->assertCreated();
        $reseauId = $response->json('data.id');

        $this->patchJson('/api/v1/reseaux/'.$reseauId, [
            'nom' => 'Mon réseau modifié',
        ])->assertOk();
        $this->patchJson('/api/v1/reseaux/'.$otherReseau->id, [
            'nom' => 'Tentative interdite',
        ])->assertNotFound();
        $this->deleteJson('/api/v1/reseaux/'.$otherReseau->id)->assertNotFound();

        $this->patchJson('/api/v1/auth/me', [
            'name' => 'Agent',
            'is_admin' => true,
        ])->assertOk();

        $this->assertFalse($agent->fresh()->is_admin);
        $this->assertDatabaseHas('reseaux', [
            'id' => $reseauId,
            'owner_user_id' => $agent->id,
            'nom' => 'Mon réseau modifié',
        ]);
    }

    public function test_any_agent_can_create_update_and_delete_their_own_networks(): void
    {
        $admin = User::factory()->create();
        DB::table('users')->where('id', $admin->id)->update(['is_admin' => true]);
        $this->actingAs($admin->fresh(), 'sanctum');

        $response = $this->postJson('/api/v1/reseaux', [
            'nom' => 'Catalogue admin',
            'code' => 'ADMIN-CATALOG',
        ])->assertCreated();

        $reseauId = $response->json('data.id');
        $this->patchJson('/api/v1/reseaux/'.$reseauId, [
            'nom' => 'Catalogue admin modifié',
        ])->assertOk();
        $this->deleteJson('/api/v1/reseaux/'.$reseauId)->assertOk();

        $this->assertSoftDeleted('reseaux', ['id' => $reseauId]);
    }

    public function test_agents_only_see_their_own_networks_and_may_reuse_another_agents_code(): void
    {
        $agentA = User::factory()->create();
        $agentB = User::factory()->create();
        $reseauA = Reseau::factory()->create([
            'owner_user_id' => $agentA->id,
            'code' => 'OM',
        ]);
        $ownedResponse = $this->actingAs($agentB, 'sanctum')
            ->postJson('/api/v1/reseaux', [
                'nom' => 'Orange Money',
                'code' => 'OM',
            ])
            ->assertCreated();
        $reseauBId = $ownedResponse->json('data.id');

        $this->actingAs($agentA, 'sanctum')
            ->getJson('/api/v1/reseaux')
            ->assertOk()
            ->assertJsonFragment(['id' => $reseauA->id])
            ->assertJsonMissing(['id' => Reseau::where('owner_user_id', $agentB->id)->value('id')]);

        $this->actingAs($agentB, 'sanctum')
            ->getJson('/api/v1/mes-reseaux')
            ->assertOk()
            ->assertJsonFragment(['id' => $reseauBId])
            ->assertJsonMissing(['id' => $reseauA->id]);
    }

    public function test_network_code_is_generated_with_a_unique_suffix_for_the_same_agent(): void
    {
        $agent = User::factory()->create();
        $this->actingAs($agent, 'sanctum');

        $this->postJson('/api/v1/reseaux', ['nom' => 'Orange Money'])
            ->assertCreated()
            ->assertJsonPath('data.code', 'OM');

        $this->postJson('/api/v1/reseaux', ['nom' => 'Orange Money'])
            ->assertCreated()
            ->assertJsonPath('data.code', 'OM-2');
    }

    public function test_generated_code_avoids_codes_on_soft_deleted_networks(): void
    {
        $agent = User::factory()->create();
        $deletedNetwork = Reseau::factory()->create([
            'owner_user_id' => $agent->id,
            'code' => 'OM',
        ]);
        $deletedNetwork->delete();

        $this->actingAs($agent, 'sanctum')
            ->postJson('/api/v1/reseaux', ['nom' => 'Orange Money'])
            ->assertCreated()
            ->assertJsonPath('data.code', 'OM-2');
    }

    public function test_agent_can_create_a_network_with_a_local_logo_uri(): void
    {
        $agent = User::factory()->create();
        $logoUri = 'file:///data/user/0/com.mobitrace/cache/network-logo.jpg';

        $this->actingAs($agent, 'sanctum')
            ->postJson('/api/v1/reseaux', [
                'nom' => 'Mon réseau',
                'logo' => $logoUri,
            ])
            ->assertCreated()
            ->assertJsonPath('data.logo', $logoUri);

        $this->assertDatabaseHas('reseaux', [
            'owner_user_id' => $agent->id,
            'logo' => $logoUri,
        ]);
    }

    public function test_agent_cannot_open_a_session_or_create_a_transaction_on_another_agents_network(): void
    {
        $agentA = User::factory()->create();
        $agentB = User::factory()->create();
        $foreignReseau = Reseau::factory()->create(['owner_user_id' => $agentA->id]);

        $this->actingAs($agentB, 'sanctum')
            ->postJson('/api/v1/caisse/sessions/ouvrir', [
                'reseau_id' => $foreignReseau->id,
                'solde_float_ouverture' => 1000,
                'solde_cash_ouverture' => 1000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reseau_id']);

        $this->postJson('/api/v1/transactions', [
            'telephone' => '70000019',
            'reseau_id' => $foreignReseau->id,
            'type_operation' => 'depot',
            'montant' => 1000,
            'client_confirme' => true,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reseau_id']);
    }
}
