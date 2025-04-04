<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResetTenantAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:reset-admin {tenant_id? : The ID of the tenant (domain without .aaccup.test)} {--show : Display the password instead of only storing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset admin password for tenant and store new password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $showPassword = $this->option('show');
        
        if (!$tenantId) {
            // List all tenants and let the user select one
            $tenants = Tenant::all();
            
            if ($tenants->isEmpty()) {
                $this->error('No tenants found');
                return 1;
            }
            
            $this->info('Select a tenant to reset admin password:');
            $tenantChoices = $tenants->pluck('id')->toArray();
            
            $tenantId = $this->choice('Which tenant?', $tenantChoices);
        }
        
        try {
            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                $this->error("Tenant with ID '$tenantId' not found");
                return 1;
            }
            
            // Generate a new password
            $password = Str::random(10);
            
            // Execute within tenant context
            $tenant->run(function () use ($tenant, $password, $showPassword) {
                try {
                    // Find the admin user(s) in this tenant
                    $adminUser = DB::table('users')->where('role', 'admin')->first();
                    
                    if (!$adminUser) {
                        $this->error('No admin user found for this tenant');
                        
                        // Get the email from tenant request
                        $tenantRequest = TenantRequest::where('domain', $tenant->id)->first();
                        
                        if ($tenantRequest) {
                            $email = $tenantRequest->email;
                            $name = $tenantRequest->department_name . ' Admin';
                            
                            // Create admin user
                            DB::table('users')->insert([
                                'name' => $name,
                                'email' => $email,
                                'password' => Hash::make($password),
                                'role' => 'admin',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            
                            $this->info("Created new admin user with email: $email");
                        } else {
                            $this->error('No tenant request found for this tenant');
                            return;
                        }
                    } else {
                        // Update existing admin password
                        DB::table('users')
                            ->where('id', $adminUser->id)
                            ->update([
                                'password' => Hash::make($password),
                                'updated_at' => now()
                            ]);
                            
                        $this->info("Reset password for admin user: {$adminUser->email}");
                    }
                    
                    // Update the stored password in tenant request record
                    DB::connection('mysql')->table('tenant_requests')
                        ->where('domain', $tenant->id)
                        ->update(['password' => $password]);
                        
                    if ($showPassword) {
                        $this->info("New password: $password");
                    } else {
                        $this->info('Password has been reset and stored in TenantRequest record');
                    }
                } catch (\Exception $e) {
                    $this->error("Error resetting password: " . $e->getMessage());
                    Log::error('Error in tenant:reset-admin command', [
                        'tenant_id' => $tenant->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            });
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error('Exception in tenant:reset-admin command', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
}
