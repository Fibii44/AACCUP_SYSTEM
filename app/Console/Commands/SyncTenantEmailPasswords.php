<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class SyncTenantEmailPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:sync-passwords {tenant_id? : The ID of the tenant} {--all : Sync all tenants} {--email : Send email with updated password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize passwords between tenant_requests and tenant users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $syncAll = $this->option('all');
        $sendEmail = $this->option('email');
        
        if ($syncAll) {
            $tenants = Tenant::all();
            
            if ($tenants->isEmpty()) {
                $this->error('No tenants found');
                return 1;
            }
            
            $this->info('Syncing passwords for all tenants...');
            
            $bar = $this->output->createProgressBar(count($tenants));
            $bar->start();
            
            foreach ($tenants as $tenant) {
                $this->syncTenantPassword($tenant, $sendEmail);
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            $this->info('All tenant passwords have been synchronized');
            
        } elseif ($tenantId) {
            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                $this->error("Tenant with ID '$tenantId' not found");
                return 1;
            }
            
            $this->syncTenantPassword($tenant, $sendEmail);
            
        } else {
            // List all tenants and let the user select one
            $tenants = Tenant::all();
            
            if ($tenants->isEmpty()) {
                $this->error('No tenants found');
                return 1;
            }
            
            $this->info('Select a tenant to sync password for:');
            $tenantChoices = $tenants->pluck('id')->toArray();
            
            $tenantId = $this->choice('Which tenant?', $tenantChoices);
            $tenant = Tenant::find($tenantId);
            
            $this->syncTenantPassword($tenant, $sendEmail);
        }
        
        return 0;
    }
    
    /**
     * Sync password for a tenant
     */
    private function syncTenantPassword(Tenant $tenant, bool $sendEmail): void
    {
        try {
            // Get tenant request
            $tenantRequest = TenantRequest::where('domain', $tenant->id)
                ->where('status', 'approved')
                ->first();
                
            if (!$tenantRequest) {
                $this->warn("No approved tenant request found for tenant: {$tenant->id}");
                return;
            }
            
            // Generate a new password if needed
            $password = $tenantRequest->password;
            if (empty($password)) {
                $password = Str::random(10);
                $tenantRequest->password = $password;
                $tenantRequest->save();
            }
            
            // Update tenant user password
            $tenant->run(function () use ($tenant, $tenantRequest, $password) {
                $user = DB::table('users')
                    ->where('email', $tenantRequest->email)
                    ->first();
                    
                if (!$user) {
                    $this->warn("No user found with email {$tenantRequest->email} in tenant {$tenant->id}");
                    return;
                }
                
                // Update user password
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'password' => Hash::make($password),
                        'updated_at' => now()
                    ]);
                    
                $this->info("Updated password for user {$user->email} in tenant {$tenant->id}");
            });
            
            if ($sendEmail) {
                // Send email with updated password
                $this->info("Sending password reminder email to {$tenantRequest->email}");
                
                Mail::to($tenantRequest->email)->send(new \App\Mail\TenantApproved($tenantRequest));
                
                $this->info("Email sent to {$tenantRequest->email}");
            }
            
            $this->info("Password synchronized for tenant: {$tenant->id}");
            if (!$sendEmail) {
                $this->info("Password: {$password}");
            }
            
        } catch (\Exception $e) {
            $this->error("Error synchronizing password for tenant {$tenant->id}: {$e->getMessage()}");
            Log::error("Error in tenant:sync-passwords", [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
