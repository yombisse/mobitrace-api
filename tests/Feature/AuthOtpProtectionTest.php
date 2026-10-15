<?php

namespace Tests\Feature;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthOtpProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_response_is_identical_for_existing_and_missing_accounts(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $missingEmail = Str::uuid().'@example.test';

        $existingResponse = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ]);
        $missingResponse = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $missingEmail,
        ]);

        $this->assertSame($existingResponse->status(), $missingResponse->status());
        $this->assertSame($existingResponse->getContent(), $missingResponse->getContent());
        $this->assertDatabaseCount('password_reset_codes', 1);
    }

    public function test_password_reset_code_is_single_use_and_expired_codes_are_rejected(): void
    {
        $user = User::factory()->create();
        $validCode = (string) random_int(100000, 999999);
        $expiredCode = (string) random_int(100000, 999999);

        $validReset = PasswordResetCode::create([
            'email' => $user->email,
            'code' => Hash::make($validCode),
            'expires_at' => now()->addMinutes(15),
        ]);
        PasswordResetCode::create([
            'email' => $user->email,
            'code' => Hash::make($expiredCode),
            'expires_at' => now()->subMinute(),
        ]);
        $newPassword = 'Passw0rd!';

        $payload = [
            'email' => $user->email,
            'code' => $validCode,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $this->postJson('/api/v1/auth/reset-password', $payload)->assertOk();
        $this->assertNotNull($validReset->fresh()->used_at);
        $this->postJson('/api/v1/auth/reset-password', $payload)->assertUnprocessable();

        $expiredPayload = [...$payload, 'code' => $expiredCode];
        $this->postJson('/api/v1/auth/reset-password', $expiredPayload)->assertUnprocessable();
    }

    public function test_login_and_otp_attempts_are_rate_limited_without_account_lockout(): void
    {
        $telephone = (string) random_int(60000000, 79999999);
        $email = Str::uuid().'@example.test';
        $password = 'Passw0rd!';

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'telephone' => $telephone,
                'password' => $password,
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/login', [
            'telephone' => $telephone,
            'password' => $password,
        ])->assertTooManyRequests();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/auth/reset-password', [
                'email' => $email,
                'code' => (string) random_int(100000, 999999),
                'password' => $password,
                'password_confirmation' => $password,
            ])->assertUnprocessable();
        }

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $email,
            'code' => (string) random_int(100000, 999999),
            'password' => $password,
            'password_confirmation' => $password,
        ])
            ->assertTooManyRequests()
            ->assertJson([
                'success' => false,
                'message' => 'Trop de tentatives. Réessayez plus tard.',
            ]);
    }

    public function test_forgot_password_sends_are_rate_limited_by_email_and_ip(): void
    {
        Mail::fake();
        $email = Str::uuid().'@example.test';

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson('/api/v1/auth/forgot-password', ['email' => $email])->assertOk();
        }

        $this->postJson('/api/v1/auth/forgot-password', ['email' => $email])
            ->assertTooManyRequests();
    }
}
