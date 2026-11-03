<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientListOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_are_listed_from_newest_to_oldest(): void
    {
        $user = User::factory()->create();

        $oldest = Client::factory()->create([
            'user_id' => $user->id,
            'telephone' => '70000001',
            'created_at' => now()->subDays(2),
        ]);
        $newest = Client::factory()->create([
            'user_id' => $user->id,
            'telephone' => '70000002',
            'created_at' => now(),
        ]);
        $middle = Client::factory()->create([
            'user_id' => $user->id,
            'telephone' => '70000003',
            'created_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/clients')
            ->assertOk();

        $this->assertSame(
            [$newest->id, $middle->id, $oldest->id],
            array_column($response->json('data'), 'id'),
        );
    }
}
