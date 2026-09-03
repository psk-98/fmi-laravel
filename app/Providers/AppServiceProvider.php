<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
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
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('api', fn(Request $request): Limit => Limit::perMinute(60)
            ->by((string) ($request->user()?->id ?? $request->ip())));
        RateLimiter::for('login', fn(Request $request): Limit => Limit::perMinute(5)
            ->by((string) $request->ip()));
        RateLimiter::for('processor', fn(Request $request): Limit => Limit::perMinute(120)
            ->by((string) $request->ip()));
    }
}
