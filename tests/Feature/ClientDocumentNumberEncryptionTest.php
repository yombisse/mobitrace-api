<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientDocumentNumberEncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_number_is_encrypted_at_rest_and_decrypted_by_eloquent(): void
    {
        $client = Client::factory()->create(['numero_piece' => 'ab 12345']);
        $storedValue = DB::table('clients')->where('id', $client->id)->value('numero_piece');

        $this->assertNotSame('ab 12345', $storedValue);
        $this->assertSame('ab 12345', $client->fresh()->numero_piece);
    }

    public function test_existing_api_response_still_returns_the_plain_document_number(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'numero_piece' => 'CNIB-TEST-API',
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/clients/'.$client->id)
            ->assertOk()
            ->assertJsonPath('data.numero_piece', 'CNIB-TEST-API');
    }

    public function test_number_search_uses_normalized_blind_index_without_changing_uniqueness(): void
    {
        $user = User::factory()->create();
        $first = Client::factory()->create([
            'user_id' => $user->id,
            'telephone' => '70110001',
            'numero_piece' => 'ab 12 345',
        ]);
        Client::factory()->create([
            'user_id' => $user->id,
            'telephone' => '70110002',
            'numero_piece' => 'AB12345',
        ]);

        $this->assertSame($first->id, Client::whereNumeroPiece(' Ab12345 ')->firstOrFail()->id);
        $this->assertSame(
            2,
            Client::query()->where('numero_piece_hash', $first->numero_piece_hash)->count(),
        );
    }

    public function test_migration_command_is_dry_run_safe_and_idempotent(): void
    {
        $user = User::factory()->create();
        $id = (string) Str::uuid();
        $plainNumber = 'CNIB-TEST-123';

        DB::table('clients')->insert([
            'id' => $id,
            'user_id' => $user->id,
            'telephone' => '70110003',
            'numero_piece' => $plainNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('cnib:encrypt-numbers', ['--dry-run' => true])
            ->expectsOutputToContain('Lignes à traiter : 1')
            ->assertExitCode(0);
        $this->assertSame($plainNumber, DB::table('clients')->where('id', $id)->value('numero_piece'));

        $this->artisan('cnib:encrypt-numbers')->expectsOutputToContain('Lignes traitées : 1')->assertExitCode(0);
        $encryptedValue = DB::table('clients')->where('id', $id)->value('numero_piece');
        $this->assertNotSame($plainNumber, $encryptedValue);
        $this->assertSame($plainNumber, Crypt::decryptString($encryptedValue));
        $this->assertNotNull(DB::table('clients')->where('id', $id)->value('numero_piece_hash'));

        $this->artisan('cnib:encrypt-numbers')->expectsOutputToContain('Lignes traitées : 0')->assertExitCode(0);
        $this->assertSame($encryptedValue, DB::table('clients')->where('id', $id)->value('numero_piece'));
    }
}
