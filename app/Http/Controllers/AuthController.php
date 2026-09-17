<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::success([
            'token' => $result['token'],
            'user' => UserResource::make($result['user']),
        ], 'Compte créé avec succès.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->string('telephone')->toString(),
            $request->string('password')->toString(),
        );

        return ApiResponse::success([
            'token' => $result['token'],
            'user' => UserResource::make($result['user']),
        ], 'Connexion réussie.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'Déconnexion réussie.');
    }

    public function me(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }

    public function updateProfile(UpdateProfileRequest $request): UserResource
    {
        $result = $this->authService->updateProfile($request->user(), $request->validated());

        return UserResource::make($result['user']);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            $request->user(),
            $request->string('current_password')->toString(),
            $request->string('password')->toString(),
        );

        return ApiResponse::success(null, 'Mot de passe modifié avec succès.');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->sendPasswordResetCode($request->string('email')->toString());

        return ApiResponse::success(null, 'Si un compte existe avec cet email, un code a été envoyé.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword(
            $request->string('email')->toString(),
            $request->string('code')->toString(),
            $request->string('password')->toString(),
        );

        return ApiResponse::success(null, 'Mot de passe réinitialisé avec succès.');
    }
}
