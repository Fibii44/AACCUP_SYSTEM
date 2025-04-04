<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateTenantDatabase extends Command
{
    protected $signature = 'tenant:create-db {tenant}';
    protected $description = 'Create and set up a database for a specific tenant';

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $this->info("Creating database for tenant: $tenantId");
        
        // Find the tenant
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant not found: $tenantId");
            return 1;
        }
        
        // Create the database
        $dbName = 'tenant_' . $tenantId;
        $this->info("Creating database: $dbName");
        
        try {
            // Check if database exists
            $dbExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);
            
            if (empty($dbExists)) {
                // Create database manually
                DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");
                $this->info("Database created successfully");
                Log::info("Database created manually for tenant", ['tenant_id' => $tenantId]);
            } else {
                $this->info("Database already exists");
                Log::info("Database already exists for tenant", ['tenant_id' => $tenantId]);
            }
            
            // Run migrations for the tenant
            $this->info("Running migrations...");
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenantId],
            ]);
            
            $output = Artisan::output();
            $this->info("Migration output:");
            $this->line($output);
            
            // Verify tables
            $this->info("Verifying tables...");
            $tenant->run(function () {
                // Directly create essential tables if they don't exist
                if (!Schema::hasTable('users')) {
                    $this->info('Creating users table directly');
                    try {
                        Schema::create('users', function ($table) {
                            $table->id();
                            $table->string('name');
                            $table->string('email')->unique();
                            $table->timestamp('email_verified_at')->nullable();
                            $table->string('password');
                            $table->string('role')->default('admin');
                            $table->rememberToken();
                            $table->timestamps();
                        });
                        $this->info('Users table created successfully');
                    } catch (\Exception $e) {
                        $this->error('Error creating users table: ' . $e->getMessage());
                        
                        // Try with raw SQL
                        try {
                            DB::statement("CREATE TABLE IF NOT EXISTS users (
                                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                name VARCHAR(255) NOT NULL,
                                email VARCHAR(255) NOT NULL UNIQUE,
                                email_verified_at TIMESTAMP NULL,
                                password VARCHAR(255) NOT NULL,
                                role VARCHAR(255) NOT NULL DEFAULT 'admin',
                                remember_token VARCHAR(100) NULL,
                                created_at TIMESTAMP NULL,
                                updated_at TIMESTAMP NULL
                            )");
                            $this->info('Users table created with raw SQL');
                        } catch (\Exception $rawEx) {
                            $this->error('Error creating users table with raw SQL: ' . $rawEx->getMessage());
                        }
                    }
                }
                
                // Create other essential tables
                $essentialTables = ['migrations', 'failed_jobs', 'password_reset_tokens', 
                                  'cache', 'cache_locks', 'jobs', 'job_batches'];
                
                foreach ($essentialTables as $tableName) {
                    if (!Schema::hasTable($tableName)) {
                        $this->info("Creating $tableName table");
                        try {
                            // Try to copy structure from central database
                            DB::statement("CREATE TABLE IF NOT EXISTS `$tableName` LIKE `aaccup`.`$tableName`");
                            $this->info("$tableName table created successfully");
                        } catch (\Exception $e) {
                            $this->error("Error creating $tableName table: " . $e->getMessage());
                        }
                    }
                }
                
                // Check tables
                $tables = DB::select('SHOW TABLES');
                echo "Tables in the database:\n";
                foreach ($tables as $table) {
                    $tableName = reset($table);
                    echo "- $tableName\n";
                }
                
                // Check users
                if (DB::table('users')->exists()) {
                    $count = DB::table('users')->count();
                    echo "Found $count users in the database\n";
                } else {
                    echo "No users found in the database\n";
                }
            });
            
            $this->info("Database setup completed successfully");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("Error setting up tenant database", [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
} 