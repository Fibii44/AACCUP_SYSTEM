<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Tenancy;
use Stancl\Tenancy\Events;
use Illuminate\Support\Facades\Event;

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
        // Register the tenant observer
        \App\Models\Tenant::observe(\App\Observers\TenantObserver::class);
        
        // Configure tenant authentication with events instead of method calls
        $this->configureTenantAuthentication();
    }
    
    /**
     * Configure authentication to use tenant's connection when in tenant context
     */
    protected function configureTenantAuthentication(): void
    {
        try {
            // Use event listeners rather than direct method calls
            Event::listen(Events\TenancyInitialized::class, function ($event) {
                // Set the user provider's connection to tenant connection
                Config::set('auth.providers.users.connection', 'tenant');
                
                Log::info('Tenant authentication configured', [
                    'tenant' => $event->tenancy->tenant->id,
                    'connection' => 'tenant'
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to configure tenant authentication: ' . $e->getMessage());
        }
    }
}
