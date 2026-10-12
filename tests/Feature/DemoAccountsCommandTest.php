<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAccountsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_command_shows_only_technical_account_identifiers(): void
    {
        $user = User::factory()->create();
        config(['legacy_demo_accounts.phones' => [$user->telephone]]);
        config(['legacy_demo_accounts.emails' => []]);

        $this->artisan('accounts:list-demo')
            ->expectsOutputToContain($user->id)
            ->doesntExpectOutputToContain($user->telephone)
            ->doesntExpectOutputToContain($user->email)
            ->assertExitCode(0);
    }

    public function test_purge_command_requires_confirmation_and_then_deletes_matches(): void
    {
        $user = User::factory()->create();
        config(['legacy_demo_accounts.phones' => [$user->telephone]]);
        config(['legacy_demo_accounts.emails' => []]);

        $this->artisan('accounts:purge-demo')
            ->expectsOutputToContain('Aucune suppression effectuée')
            ->assertExitCode(0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $this->artisan('accounts:purge-demo', ['--confirm' => true])
            ->expectsOutputToContain('Comptes supprimés : 1')
            ->assertExitCode(0);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
