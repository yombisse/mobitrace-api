<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthenticationException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json(['message' => 'Unauthenticated.'], 401);
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json(['message' => 'Ressource introuvable.'], 404);
        });

        $exceptions->render(function (ValidationException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $exception->errors(),
            ], 422);
        });
    })->create();
