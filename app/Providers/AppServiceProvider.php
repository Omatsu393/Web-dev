<?php

namespace App\Providers;

use App\Services\Routing\GoogleRoutesProvider;
use App\Services\Routing\RouteComparisonProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            RouteComparisonProvider::class,
            fn (): GoogleRoutesProvider => new GoogleRoutesProvider(
                apiKey: (string) config('services.google_routes.api_key'),
                endpoint: (string) config('services.google_routes.endpoint'),
            ),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
