<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CopyGoogleCredentials extends Command
{
    protected $signature = 'google:copy-credentials {tenant?} {--all}';
    protected $description = 'Copy Google credentials to tenant storage';

    public function handle()
    {
        $globalCredentialsPath = base_path(config('google.credentials_path'));
        
        if (!file_exists($globalCredentialsPath)) {
            $this->error("Global credentials file not found at: {$globalCredentialsPath}");
            return 1;
        }

        if ($this->option('all')) {
            $tenants = \App\Models\Tenant::all();
            foreach ($tenants as $tenant) {
                $this->copyCredentialsForTenant($tenant, $globalCredentialsPath);
            }
        } else {
            $tenantId = $this->argument('tenant');
            if (!$tenantId) {
                $this->error('Please specify a tenant ID or use --all option');
                return 1;
            }

            $tenant = \App\Models\Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("Tenant not found: {$tenantId}");
                return 1;
            }

            $this->copyCredentialsForTenant($tenant, $globalCredentialsPath);
        }

        return 0;
    }

    protected function copyCredentialsForTenant($tenant, $globalCredentialsPath)
    {
        $tenant->run(function () use ($globalCredentialsPath, $tenant) {
            $contents = file_get_contents($globalCredentialsPath);
            Storage::disk('tenant')->put('google-credentials.json', $contents);
            $this->info("Copied credentials for tenant: {$tenant->id}");
        });
    }
} 