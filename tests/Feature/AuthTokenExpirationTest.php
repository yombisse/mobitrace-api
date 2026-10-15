<?php

namespace Tests\Feature;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthTokenExpirationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_sanctum_token_is_accepted(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile');

        $this->withToken($token->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_expired_sanctum_token_returns_the_api_json_401_envelope(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile');

        DB::table('personal_access_tokens')
            ->where('id', $token->accessToken->id)
            ->update(['created_at' => now()->subMinutes(43201)]);

        $this->withToken($token->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertExactJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile');

        $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_password_change_revokes_other_tokens_but_keeps_current_token(): void
    {
        $currentPassword = 'Passw0rd!';
        $newPassword = 'N3wP@ss!';
        $user = User::factory()->create(['password' => $currentPassword]);
        $currentToken = $user->createToken('mobile');
        $otherToken = $user->createToken('mobile');

        $this->withToken($currentToken->plainTextToken)
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => $currentPassword,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ])
            ->assertOk();

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $currentToken->accessToken->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $otherToken->accessToken->id]);
    }

    public function test_password_reset_revokes_all_tokens(): void
    {
        $user = User::factory()->create();
        $firstToken = $user->createToken('mobile');
        $secondToken = $user->createToken('mobile');
        $code = (string) random_int(100000, 999999);
        PasswordResetCode::create([
            'email' => $user->email,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        $newPassword = 'N3wP@ss!';
        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'code' => $code,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ])->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $firstToken->accessToken->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $secondToken->accessToken->id]);
    }
}
