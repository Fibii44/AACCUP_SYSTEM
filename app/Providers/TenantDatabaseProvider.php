<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantDatabaseProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (tenant()) {
            $this->configureTenantConnection();
        }
    }

    /**
     * Configure the tenant database connection
     */
    protected function configureTenantConnection(): void
    {
        $tenantId = tenant()->getTenantKey();
        
        // Set the database name for the tenant connection
        Config::set('database.connections.tenant.database', 'tenant_' . $tenantId);
        
        // Set tenant connection as default for all tenant operations
        Config::set('database.default', 'tenant');
        
        // Purge the current connection and reconnect
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Log the current configuration for debugging
        \Log::info("Tenant database configured", [
            'tenant' => $tenantId,
            'database' => Config::get('database.connections.tenant.database'),
            'default_connection' => Config::get('database.default')
        ]);
    }
} 