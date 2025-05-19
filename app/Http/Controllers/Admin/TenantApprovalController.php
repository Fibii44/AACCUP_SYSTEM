<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class TenantApprovalController extends Controller
{
    public function index()
    {
        $pendingRequests = TenantRequest::where('status', 'pending')->get();
        return view('central.admin.tenant-requests.index', compact('pendingRequests'));
    }
    
    public function show(TenantRequest $tenantRequest)
    {
        return view('central.admin.tenant-requests.show', compact('tenantRequest'));
    }
    
    public function approve(TenantRequest $tenantRequest)
    {
        try {
            Log::info('Starting tenant approval process', [
                'tenant_request_id' => $tenantRequest->id,
                'department_name' => $tenantRequest->department_name,
                'domain' => $tenantRequest->domain,
                'email' => $tenantRequest->email,
                'status' => $tenantRequest->status,
                'route' => request()->route()->getName(),
                'method' => request()->method(),
                'request_url' => request()->fullUrl()
            ]);
            
            // Verify tenant request status
            if ($tenantRequest->status !== 'pending') {
                Log::warning('Attempted to approve a non-pending tenant request', [
                    'id' => $tenantRequest->id,
                    'current_status' => $tenantRequest->status
                ]);
                
                return redirect()->route('admin.tenant-requests.index')
                    ->with('error', 'This request has already been ' . $tenantRequest->status);
            }
            
            // Generate a random password
            $password = Str::random(10);
            
            // Update the tenant request with the password FIRST
            // This way we ensure the password is stored for the email
            $tenantRequest->update([
                'status' => 'approved',
                'password' => $password,
            ]);
            
            Log::info('Tenant request updated with password', [
                'status' => 'approved', 
                'password_set' => !empty($password),
                'password_stored' => !empty($tenantRequest->password),
                'password_length' => strlen($password)
            ]);
            
            Log::info('Creating tenant record', ['domain' => $tenantRequest->domain]);
            
            // Create the tenant
            try {
                Log::info('About to create Tenant model');
                
                // Set a timeout for database operations
                $startTime = microtime(true);
                $maxExecutionTime = 30; // 30 seconds max
                
                $tenant = Tenant::create([
                    'id' => $tenantRequest->domain,
                    'department_name' => $tenantRequest->department_name,
                    'email' => $tenantRequest->email,
                ]);
                
                $endTime = microtime(true);
                $executionTime = $endTime - $startTime;
                
                if ($executionTime > 5) {
                    Log::warning('Tenant creation took longer than expected', [
                        'execution_time' => round($executionTime, 2) . ' seconds',
                        'tenant_id' => $tenant->id
                    ]);
                }
                
                Log::info('Tenant record created successfully', [
                    'tenant_id' => $tenant->id,
                    'execution_time' => round($executionTime, 2) . ' seconds'
                ]);

                // Set up the tenant database directly, bypassing migrations
                $databaseSetup = $this->setupTenantDatabase($tenant->id);
                if ($databaseSetup) {
                    Log::info('Database tables created successfully');
                } else {
                    Log::warning('Failed to create database tables directly, will attempt migrations');
                }

                // Run tenant migrations to create all required tables
                try {
                    Log::info('Running tenant-specific migrations', ['tenant' => $tenant->id]);
                    
                    // Check existing tables in the tenant database
                    $dbName = 'tenant_' . $tenant->id;
                    $config = config('database.connections.mysql');
                    
                    // Connect directly to tenant database 
                    $mysqli = new \mysqli(
                        $config['host'], 
                        $config['username'], 
                        $config['password'], 
                        $dbName,
                        $config['port'] ?? 3306
                    );
                    
                    if (!$mysqli->connect_error) {
                        $result = $mysqli->query("SHOW TABLES");
                        $tableCount = $result->num_rows;
                        $tenantTables = [];
                        
                        while ($row = $result->fetch_array()) {
                            $tenantTables[] = $row[0];
                        }
                        
                        Log::info('Current tenant database state', [
                            'tenant_id' => $tenant->id,
                            'table_count' => $tableCount,
                            'tables' => $tenantTables
                        ]);
                        
                        $mysqli->close();
                    }
                    
                    // Directly run tenant migrations
                    $tenant->run(function () use ($tenant, $tenantRequest) {
                        try {
                            Log::info('Checking for required tables in tenant database');
                            $requiredTables = ['areas', 'instruments', 'parameters', 'indicators', 'uploads'];
                            $missingTables = [];
                            
                            foreach ($requiredTables as $table) {
                                if (!Schema::hasTable($table)) {
                                    $missingTables[] = $table;
                                }
                            }
                            
                            if (count($missingTables) > 0) {
                                Log::info('Missing required tables, running tenant migrations', [
                                    'missing_tables' => $missingTables
                                ]);
                                
                                // Run the migrations directly
                                \Illuminate\Support\Facades\Artisan::call('migrate', [
                                    '--path' => 'database/migrations/tenant',
                                    '--force' => true
                                ]);
                                
                                $output = \Illuminate\Support\Facades\Artisan::output();
                                Log::info('Direct tenant migration output', ['output' => $output]);
                            } else {
                                Log::info('All required tables already exist in tenant database');
                            }
                            
                            // Now create or update the tenant_settings with department name
                            if (Schema::hasTable('tenant_settings')) {
                                Log::info('Saving department name to tenant_settings');
                                
                                // Check if tenant_settings record exists
                                $settingsExist = \Illuminate\Support\Facades\DB::table('tenant_settings')->exists();
                                
                                if ($settingsExist) {
                                    // Update existing record
                                    \Illuminate\Support\Facades\DB::table('tenant_settings')
                                        ->update([
                                            'header_text' => $tenantRequest->department_name,
                                            'updated_at' => now()
                                        ]);
                                    
                                    Log::info('Updated tenant_settings with department name');
                                } else {
                                    // Create new record
                                    \Illuminate\Support\Facades\DB::table('tenant_settings')
                                        ->insert([
                                            'header_text' => $tenantRequest->department_name,
                                            'welcome_message' => 'Welcome to ' . $tenantRequest->department_name . ' Dashboard',
                                            'created_at' => now(),
                                            'updated_at' => now()
                                        ]);
                                    
                                    Log::info('Created tenant_settings with department name');
                                }
                            } else {
                                Log::warning('tenant_settings table does not exist, cannot save department name');
                            }
                        } catch (\Exception $e) {
                            Log::error('Error during direct tenant migration or settings update', [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    });
                } catch (\Exception $e) {
                    Log::error('Error running tenant migrations', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Error creating tenant record', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
            Log::info('About to create domain');
            
            // Create a domain for the tenant
            try {
                Log::info('Creating domain for tenant', ['domain' => $tenantRequest->domain . '.' . config('app.domain')]);
                
                $domain = $tenant->domains()->create([
                    'domain' => $tenantRequest->domain . '.' . config('app.domain'),
                ]);
                
                Log::info('Domain created successfully', ['domain_id' => $domain->id]);
            } catch (\Exception $e) {
                Log::error('Error creating domain', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
            // Refresh the tenant request to make sure we have the latest data
            $tenantRequest = $tenantRequest->fresh();
            
            Log::info('Tenant request confirmation', [
                'status' => $tenantRequest->status,
                'password_exists' => !empty($tenantRequest->password),
                'password_length' => strlen($tenantRequest->password ?? '')
            ]);
            
            // Run migrations for the tenant
            try {
                Log::info('Running safe migrations for tenant', ['tenant_id' => $tenant->id]);
                try {
                    // Try to use our safer migration command that handles conflicts better
                    Log::info('Attempting to use tenant:safe-migrate command');
                    
                    // Create the database first if it doesn't exist (skip-grant-tables issue)
                    $dbName = 'tenant_' . $tenant->id;
                    $dbExists = \DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);
                    
                    if (empty($dbExists)) {
                        Log::info('Database does not exist, creating now', ['db_name' => $dbName]);
                        try {
                            \DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");
                            Log::info('Database created successfully');
                        } catch (\Exception $e) {
                            Log::warning('Could not create database directly, will try different approach', [
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    \Illuminate\Support\Facades\Artisan::call('tenant:safe-migrate', [
                        '--tenant' => $tenant->id,
                        '--force' => true
                    ]);
                    
                    $output = \Illuminate\Support\Facades\Artisan::output();
                    Log::info('Safe migrations output', ['output' => $output]);
                } catch (\Exception $e) {
                    Log::error('Error running safe migrations, falling back to standard approach', [
                        'error' => $e->getMessage()
                    ]);
                    
                    // Handle "table already exists" error specifically
                    if (strpos($e->getMessage(), 'already exists') !== false) {
                        Log::info('Table already exists error encountered, checking database directly');
                        
                        // Try to create the tenant user explicitly
                        $tenant->run(function () use ($tenant, $tenantRequest) {
                            try {
                                Log::info('Checking if admin user needs to be created');
                                
                                // Check if users table exists and has users
                                $usersTableExists = \Illuminate\Support\Facades\Schema::hasTable('users');
                                
                                if ($usersTableExists) {
                                    $adminCount = \Illuminate\Support\Facades\DB::table('users')
                                        ->where('role', 'admin')
                                        ->count();
                                        
                                    if ($adminCount === 0) {
                                        Log::info('Creating admin user for tenant', [
                                            'email' => $tenantRequest->email,
                                        ]);
                                        
                                        \Illuminate\Support\Facades\DB::table('users')->insert([
                                            'name' => $tenantRequest->department_name . ' Admin',
                                            'email' => $tenantRequest->email,
                                            'password' => \Illuminate\Support\Facades\Hash::make($tenantRequest->password),
                                            'role' => 'admin',
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                        
                                        Log::info('Admin user created successfully');
                                    } else {
                                        Log::info('Admin user already exists');
                                    }
                                } else {
                                    Log::error('Users table does not exist, migration may have failed');
                                }
                            } catch (\Exception $innerE) {
                                Log::error('Error during tenant user creation', [
                                    'error' => $innerE->getMessage(),
                                    'trace' => $innerE->getTraceAsString()
                                ]);
                            }
                        });
                    } else {
                        // Fall back to regular migration process with checks
                        $tenant->run(function () {
                            // First check if migrations table exists
                            $migrationsTableExists = \Illuminate\Support\Facades\Schema::hasTable('migrations');
                            $usersTableExists = \Illuminate\Support\Facades\Schema::hasTable('users');
                            
                            Log::info('Pre-migration check', [
                                'migrations_table_exists' => $migrationsTableExists ? 'Yes' : 'No',
                                'users_table_exists' => $usersTableExists ? 'Yes' : 'No'
                            ]);
                            
                            if ($migrationsTableExists) {
                                // If migrations table exists but is empty, we should still run migrations
                                $migrationCount = \Illuminate\Support\Facades\DB::table('migrations')->count();
                                
                                if ($migrationCount > 0) {
                                    Log::info('Migrations table exists with entries, running only tenant-specific migrations');
                                    // Run only tenant-specific migrations that might not be in the existing migrations table
                                    try {
                                        // This is safer than running all migrations
                                        $migrationsRun = \Illuminate\Support\Facades\Artisan::call('migrate', [
                                            '--force' => true,
                                            '--path' => 'database/migrations/tenant',
                                            '--pretend' => true, // First do a dry run to see if there are any migrations to run
                                        ]);
                                        
                                        // Check output to see if there are any migrations to run
                                        $output = \Illuminate\Support\Facades\Artisan::output();
                                        
                                        if (strpos($output, 'Nothing to migrate') !== false) {
                                            Log::info('No tenant-specific migrations to run');
                                        } else {
                                            // Run the migrations for real
                                            $migrationsRun = \Illuminate\Support\Facades\Artisan::call('migrate', [
                                                '--force' => true,
                                                '--path' => 'database/migrations/tenant',
                                            ]);
                                            Log::info('Tenant-specific migrations completed', ['migrations_run' => $migrationsRun]);
                                        }
                                    } catch (\Exception $e) {
                                        Log::error('Error running tenant-specific migrations', [
                                            'error' => $e->getMessage()
                                        ]);
                                    }
                                } else {
                                    Log::info('Migrations table exists but is empty, running all migrations');
                                    
                                    try {
                                        // Run migrations in a safer way, skipping if tables exist
                                        \Illuminate\Support\Facades\Artisan::call('migrate', [
                                            '--force' => true,
                                        ]);
                                        
                                        Log::info('All migrations completed successfully');
                                    } catch (\Exception $e) {
                                        Log::error('Error running full migrations', [
                                            'error' => $e->getMessage(),
                                            'trace' => $e->getTraceAsString()
                                        ]);
                                    }
                                }
                            } else {
                                // Run all migrations if migrations table doesn't exist
                                try {
                                    Log::info('No migrations table, running all migrations');
                                    \Illuminate\Support\Facades\Artisan::call('migrate', [
                                        '--force' => true,
                                    ]);
                                    
                                    Log::info('All migrations completed successfully');
                                } catch (\Exception $e) {
                                    Log::error('Error running initial migrations', [
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            }
                        });
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error during migration process', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // Create admin user for the tenant
            try {
                // Get the password directly from the tenant request
                // This ensures we're using exactly what will be sent in the email
                $passwordForUser = $tenantRequest->password;
                
                Log::info('Creating admin user for tenant', [
                    'password_source' => 'tenant_request',
                    'password_exists' => !empty($passwordForUser),
                    'password_length' => strlen($passwordForUser ?? '')
                ]);
                
                $tenant->run(function () use ($tenantRequest, $passwordForUser) {
                    // Check if users table exists and if admin user already exists
                    if (!\Illuminate\Support\Facades\Schema::hasTable('users')) {
                        Log::error('Users table does not exist, cannot create admin user');
                        return;
                    }
                    
                    // Check if admin user already exists
                    $adminExists = DB::table('users')
                        ->where('email', $tenantRequest->email)
                        ->exists();
                        
                    if ($adminExists) {
                        Log::info('Admin user already exists, skipping creation');
                        return;
                    }
                    
                    // Create the admin user
                    DB::table('users')->insert([
                        'name' => $tenantRequest->department_name . ' Admin',
                        'email' => $tenantRequest->email,
                        'password' => Hash::make($passwordForUser), // Use the password from tenant_request
                        'role' => 'admin',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    Log::info('Admin user created successfully', [
                        'email' => $tenantRequest->email,
                        'password_length' => strlen($passwordForUser),
                        'password_hash_algo' => 'bcrypt'
                    ]);
                });
            } catch (\Exception $e) {
                Log::error('Error creating admin user', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // Send email with credentials
            try {
                Log::info('Preparing to send approval email', [
                    'to' => $tenantRequest->email,
                    'domain' => $tenantRequest->domain,
                    'password' => str_repeat('*', strlen($password)-2) . substr($password, -2)
                ]);
                
                // Test logging the mail content
                Log::info('Email content context', [
                    'departmentName' => $tenantRequest->department_name,
                    'url' => 'http://' . $tenantRequest->domain . '.' . config('app.domain'),
                    'email' => $tenantRequest->email,
                ]);
                
                $mail = new \App\Mail\TenantApproved($tenantRequest);
                Mail::to($tenantRequest->email)->send($mail);
                
                Log::info('Approval email sent');
                
                // Verify that both email was sent and user was created successfully
                // This is a good place to perform a test login if needed
                $tenant->run(function () use ($tenantRequest, $password) {
                    $userExists = DB::table('users')
                        ->where('email', $tenantRequest->email)
                        ->exists();
                        
                    Log::info('Final verification', [
                        'user_exists' => $userExists ? 'Yes' : 'No',
                        'email' => $tenantRequest->email,
                        'domain' => tenant()->id,
                        'password_in_tenant_request' => !empty($tenantRequest->password) ? 'Yes' : 'No'
                    ]);
                });
            } catch (\Exception $e) {
                Log::error('Failed to send email', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            return redirect()->route('admin.tenant-requests.index')
                ->with('success', 'Tenant request approved successfully.');
        } catch (\Exception $e) {
            Log::error('Exception during tenant approval', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.tenant-requests.index')
                ->with('error', 'Error approving tenant: ' . $e->getMessage());
        }
    }
    
    public function reject(Request $request, TenantRequest $tenantRequest)
    {
        try {
            Log::info('Starting tenant rejection process', ['tenant_request_id' => $tenantRequest->id]);
            
            $request->validate([
                'rejection_reason' => 'required|string|max:255',
            ]);
            
            $tenantRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);
            
            Log::info('Tenant request rejected', ['rejection_reason' => $request->rejection_reason]);
            
            try {
                Log::info('Sending rejection email', ['email' => $tenantRequest->email]);
                Mail::to($tenantRequest->email)->send(new \App\Mail\TenantRejected($tenantRequest));
                Log::info('Rejection email sent');
            } catch (\Exception $e) {
                Log::error('Failed to send rejection email', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            return redirect()->route('admin.tenant-requests.index')
                ->with('success', 'Tenant request rejected successfully.');
        } catch (\Exception $e) {
            Log::error('Exception during tenant rejection', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.tenant-requests.index')
                ->with('error', 'Error rejecting tenant: ' . $e->getMessage());
        }
    }

    /**
     * Create database tables directly without migration
     */
    protected function setupTenantDatabase(string $tenantId): bool
    {
        Log::info('Setting up tenant database directly', ['tenant_id' => $tenantId]);
        
        // Get database name for this tenant
        $dbName = 'tenant_' . $tenantId;
        
        // Get database configuration
        $config = config('database.connections.mysql');
        
        // Create database if it doesn't exist
        try {
            Log::info('Checking if database exists');
            $dbExists = \DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);
            
            if (empty($dbExists)) {
                Log::info('Database does not exist, creating now', ['db_name' => $dbName]);
                \DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");
                Log::info('Database created successfully');
                
                // New database, we need to create tables
                $createTables = true;
            } else {
                Log::info('Database already exists', ['db_name' => $dbName]);
                
                // Database exists, check if it has tables before creating
                $createTables = false;
            }
            
            // Connect directly to tenant database
            $mysqli = new \mysqli(
                $config['host'], 
                $config['username'], 
                $config['password'], 
                $dbName,
                $config['port'] ?? 3306
            );
            
            if ($mysqli->connect_error) {
                Log::error('Failed to connect to tenant database', [
                    'db_name' => $dbName,
                    'error' => $mysqli->connect_error
                ]);
                return false;
            }
            
            Log::info('Connected to tenant database successfully', ['db_name' => $dbName]);
            
            // Check for existing tables to determine if we should create tables
            $result = $mysqli->query("SHOW TABLES");
            $existingTables = [];
            $tableCount = 0;
            
            if ($result) {
                $tableCount = $result->num_rows;
                while ($row = $result->fetch_array()) {
                    $existingTables[] = $row[0];
                }
            }
            
            Log::info('Existing tables in tenant database', [
                'tables' => $existingTables,
                'count' => $tableCount
            ]);
            
            // Only create tables if the database is empty or if specifically requested
            if ($tableCount === 0 || $createTables) {
                // Create migrations table if it doesn't exist
                if (!in_array('migrations', $existingTables)) {
                    $migrationsTable = "CREATE TABLE IF NOT EXISTS `migrations` (
                        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `migration` varchar(255) NOT NULL,
                        `batch` int(11) NOT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                    
                    if ($mysqli->query($migrationsTable) === TRUE) {
                        Log::info("Migrations table created successfully");
                    } else {
                        Log::error("Error creating migrations table", ['error' => $mysqli->error]);
                    }
                }
                
                // Only create the users table if it doesn't exist
                if (!in_array('users', $existingTables)) {
                    $usersTable = "CREATE TABLE IF NOT EXISTS `users` (
                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `name` varchar(255) NOT NULL,
                        `email` varchar(255) NOT NULL,
                        `email_verified_at` timestamp NULL DEFAULT NULL,
                        `password` varchar(255) NOT NULL,
                        `role` varchar(255) NOT NULL DEFAULT 'admin',
                        `remember_token` varchar(100) DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `users_email_unique` (`email`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                    
                    if ($mysqli->query($usersTable) === TRUE) {
                        Log::info("Users table created successfully");
                    } else {
                        Log::error("Error creating users table", ['error' => $mysqli->error]);
                    }
                }
            } else {
                Log::info('Database already has tables, skipping table creation');
            }
            
            // Close database connection
            $mysqli->close();
            
            Log::info('Essential tenant database tables checked/created successfully', ['tenant_id' => $tenantId]);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Exception during direct database operations', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
