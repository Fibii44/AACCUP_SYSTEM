<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProviderCustom extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the TenantRouteServiceProvider
        $this->app->register(TenantRouteServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
