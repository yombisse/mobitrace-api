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

    public function test_regular_agent_cannot_create_update_or_delete_networks_or_grant_admin_role(): void
    {
        $agent = User::factory()->create();
        $reseau = Reseau::factory()->create();
        $this->actingAs($agent, 'sanctum');

        $this->postJson('/api/v1/reseaux', [
            'nom' => 'Réseau interdit',
        ])->assertForbidden();
        $this->patchJson('/api/v1/reseaux/'.$reseau->id, [
            'nom' => 'Réseau modifié',
        ])->assertForbidden();
        $this->deleteJson('/api/v1/reseaux/'.$reseau->id)->assertForbidden();

        $this->patchJson('/api/v1/auth/me', [
            'name' => 'Agent',
            'is_admin' => true,
        ])->assertOk();

        $this->assertFalse($agent->fresh()->is_admin);
        $this->assertDatabaseMissing('reseaux', ['nom' => 'Réseau interdit']);
    }

    public function test_administrator_can_create_update_and_delete_networks(): void
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

    public function test_regular_agent_can_still_read_the_network_catalogue(): void
    {
        $agent = User::factory()->create();
        $reseau = Reseau::factory()->create();
        $this->actingAs($agent, 'sanctum');

        $this->getJson('/api/v1/reseaux')
            ->assertOk()
            ->assertJsonFragment(['id' => $reseau->id]);
    }
}
