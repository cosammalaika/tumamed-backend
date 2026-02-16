<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
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
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        RateLimiter::for('patient', function (Request $request) {
            $phone = strtolower(trim((string) ($request->input('phone') ?? $request->header('X-Patient-Phone', ''))));
            $ip = (string) $request->ip();
            $key = $phone !== '' ? "{$ip}|{$phone}" : $ip;

            return Limit::perMinute(30)->by($key);
        });
    }
}
