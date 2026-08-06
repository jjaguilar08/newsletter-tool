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
        // Public, unauthenticated mail-sending endpoint - IP is the only
        // signal available to throttle on, since there's no authenticated
        // user to key against.
        RateLimiter::for('sample-newsletter', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });
    }
}
