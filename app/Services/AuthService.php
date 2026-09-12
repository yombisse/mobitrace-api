<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    /**
     * @return array{token: string, user: User}
     */
    public function register(array $data): array
    {
        $user = User::create([
            ...$data,
            'code_agent' => $this->generateAgentCode(),
        ]);

        return $this->issueToken($user);
    }

    /**
     * @return array{token: string, user: User}
     */
    public function login(string $telephone, string $password): array
    {
        $user = User::where('telephone', $telephone)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw new ApiException('Identifiants invalides.', 401);
        }

        return $this->issueToken($user);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * @return array{token: string, user: User}
     */
    public function updateProfile(User $user, array $data): array
    {
        $user->fill($data);
        $user->save();

        return [
            'token' => '',
            'user' => $user->refresh(),
        ];
    }

    public function changePassword(User $user, string $currentPassword, string $password): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new ApiException('Le mot de passe actuel est incorrect.', 422);
        }

        $currentTokenId = $user->currentAccessToken()?->getKey();

        $user->update(['password' => $password]);

        $query = $user->tokens();
        if ($currentTokenId !== null) {
            $query->whereKeyNot($currentTokenId);
        }
        $query->delete();
    }

    public function sendPasswordResetCode(string $email): void
    {
        $user = User::where('email', $email)->first();

        if ($user === null) {
            return;
        }

        $code = (string) random_int(100000, 999999);

        PasswordResetCode::where('email', $email)->delete();
        PasswordResetCode::create([
            'email' => $email,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::raw(
            "Votre code de réinitialisation Mobitrace est : {$code}. Il est valable 15 minutes.",
            function ($message) use ($user): void {
                $message->to($user->email)
                    ->subject('Code de réinitialisation Mobitrace');
            },
        );
    }

    public function resetPassword(string $email, string $code, string $password): void
    {
        DB::transaction(function () use ($email, $code, $password): void {
            $resetCode = PasswordResetCode::where('email', $email)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest('created_at')
                ->first();

            if ($resetCode === null || ! Hash::check($code, $resetCode->code)) {
                throw new ApiException('Code invalide ou expiré.', 422);
            }

            $user = User::where('email', $email)->first();
            if ($user === null) {
                throw new ApiException('Code invalide ou expiré.', 422);
            }

            $user->update(['password' => $password]);
            $user->tokens()->delete();
            $resetCode->update(['used_at' => now()]);
        });
    }

    /**
     * @return array{token: string, user: User}
     */
    private function issueToken(User $user): array
    {
        return [
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $user,
        ];
    }

    private function generateAgentCode(): string
    {
        do {
            $code = 'AG-'.str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (User::where('code_agent', $code)->exists());

        return $code;
    }
}
