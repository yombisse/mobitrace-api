<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute((int) env('API_RATE_LIMIT', 60))
                ->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request): Limit {
            return Limit::perMinute((int) env('AUTH_RATE_LIMIT', 10))
                ->by($request->input('telephone', $request->ip()).'|'.$request->ip());
        });

        RateLimiter::for('forgot-password', function (Request $request): Limit {
            return Limit::perMinute((int) env('FORGOT_PASSWORD_RATE_LIMIT', 3))
                ->by($request->input('email', $request->ip()).'|'.$request->ip());
        });
    }
}
