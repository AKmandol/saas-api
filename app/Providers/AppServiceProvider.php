<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();

            if ($user) {
                return Limit::perMinute(60)
                    ->by('user:' . $user->id);
            }

            return Limit::perMinute(30)
                ->by('ip:' . $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by(
                    strtolower((string) $request->input('email'))
                    . '|' .
                    $request->ip()
                );
        });
    }
}
