<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyCnibImageCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_image_fields_are_ignored_when_updating_a_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patchJson('/api/v1/clients/'.$client->id, [
                'nom' => $client->nom,
                'cni_recto' => 'legacy-recto-payload',
                'cni_verso' => 'legacy-verso-payload',
            ])
            ->assertOk()
            ->assertJsonMissingPath('data.cni_recto')
            ->assertJsonMissingPath('data.cni_verso');

        $this->assertFalse(Schema::hasColumn('clients', 'cni_recto'));
        $this->assertFalse(Schema::hasColumn('clients', 'cni_verso'));
    }
}
