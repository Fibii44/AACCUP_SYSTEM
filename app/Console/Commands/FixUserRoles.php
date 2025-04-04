<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class FixUserRoles extends Command
{
    protected $signature = 'fix:user-roles';
    protected $description = 'Add missing role fields to users in all databases';

    public function handle()
    {
        $this->info("Starting to fix user roles...");
        
        // Step 1: Fix central database
        $this->info("Checking central database...");
        $this->fixDatabaseRoles('central');
        
        // Step 2: Fix tenant databases
        $tenants = Tenant::all();
        $this->info("Found " . $tenants->count() . " tenants to check");
        
        foreach ($tenants as $tenant) {
            $this->info("Checking tenant: " . $tenant->id);
            
            $tenant->run(function () use ($tenant) {
                $this->fixDatabaseRoles('tenant: ' . $tenant->id);
            });
        }
        
        $this->info("User role fix completed");
        return 0;
    }
    
    protected function fixDatabaseRoles($context)
    {
        try {
            // Check if users table exists
            if (!Schema::hasTable('users')) {
                $this->warn("[$context] Users table does not exist");
                return;
            }
            
            // Check if role column exists
            if (!Schema::hasColumn('users', 'role')) {
                $this->info("[$context] Adding role column to users table");
                
                // Add the column
                Schema::table('users', function ($table) {
                    $table->string('role')->default('admin')->after('password');
                });
                
                $this->info("[$context] Role column added successfully");
            } else {
                $this->info("[$context] Role column already exists");
            }
            
            // Check for users without a role value
            $usersWithoutRole = DB::table('users')
                ->whereNull('role')
                ->orWhere('role', '')
                ->count();
                
            if ($usersWithoutRole > 0) {
                $this->info("[$context] Updating $usersWithoutRole users with missing role values");
                
                // Set role to 'admin' for all users without a role
                DB::table('users')
                    ->whereNull('role')
                    ->orWhere('role', '')
                    ->update(['role' => 'admin']);
                    
                $this->info("[$context] Users updated successfully");
            } else {
                $this->info("[$context] All users have role values");
            }
            
            // Print user counts by role
            $usersByRole = DB::table('users')
                ->select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get();
                
            foreach ($usersByRole as $roleCount) {
                $this->info("[$context] Users with role '{$roleCount->role}': {$roleCount->count}");
            }
            
        } catch (\Exception $e) {
            $this->error("[$context] Error: " . $e->getMessage());
            Log::error("Error fixing roles in $context", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 