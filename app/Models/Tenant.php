<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected static function booted()
    {
        static::created(function ($tenant) {
            // Log tenant creation
            Log::info('Tenant created event triggered', ['tenant_id' => $tenant->id]);
            
            // Use the custom command to create the database and run migrations
            try {
                // Run in a separate process to avoid connection issues
                Log::info('Running tenant:create-db command', ['tenant_id' => $tenant->id]);
                Artisan::call('tenant:create-db', [
                    'tenant' => $tenant->id,
                ]);
                
                $output = Artisan::output();
                Log::info('Create database command output', [
                    'tenant_id' => $tenant->id, 
                    'output' => $output
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to run tenant:create-db command', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'department_name',
            'email',
        ];
    }
}
