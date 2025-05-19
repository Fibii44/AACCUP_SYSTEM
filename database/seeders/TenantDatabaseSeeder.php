<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant's database.
     */
    public function run(): void
    {
        // Get tenant information
        $tenant = app(\Stancl\Tenancy\Contracts\Tenant::class);
        $centralConnection = config('tenancy.database.central_connection');
        
        try {
            // First, ensure the users table exists
            if (!DB::getSchemaBuilder()->hasTable('users')) {
                // Create the users table if it doesn't exist
                \Illuminate\Support\Facades\Schema::create('users', function ($table) {
                    $table->id();
                    $table->string('name');
                    $table->string('email')->unique();
                    $table->timestamp('email_verified_at')->nullable();
                    $table->string('password');
                    $table->string('role')->default('user');
                    $table->rememberToken();
                    $table->timestamps();
                });
            }
            
            // Get the password from the tenant_requests table
            $tenantRequest = DB::connection($centralConnection)
                ->table('tenant_requests')
                ->where('domain', $tenant->id)
                ->first();
                
            if ($tenantRequest) {
                $password = $tenantRequest->password ?? Str::random(10);
                $departmentName = $tenantRequest->department_name ?? 'Admin';
                $email = $tenantRequest->email ?? 'admin@' . $tenant->id . '.' . config('app.domain');
                
                // Check if the user already exists
                $existingUser = DB::table('users')->where('email', $email)->first();
                
                if (!$existingUser) {
                    // Insert admin user
                    DB::table('users')->insert([
                        'name' => $departmentName . ' Admin',
                        'email' => $email,
                        'password' => Hash::make($password),
                        'role' => 'admin',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    \Illuminate\Support\Facades\Log::info("Created admin user for tenant", [
                        'tenant' => $tenant->id,
                        'email' => $email
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error creating tenant admin: " . $e->getMessage(), [
                'tenant' => $tenant->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 