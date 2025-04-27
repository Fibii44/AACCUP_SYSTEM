<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class TenantSafeMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:safe-migrate 
                            {--tenant= : The ID of the tenant to migrate}
                            {--force : Force the operation to run when in production}
                            {--path= : The path to the migrations files to be executed}
                            {--force-run : Force migrations to run even if nothing is found to migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tenant migrations safely, handling table existence conflicts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting safe tenant migration');
        
        $tenantId = $this->option('tenant');
        if (!$tenantId) {
            $this->error('No tenant ID provided');
            return 1;
        }
        
        // Get the tenant
        $tenant = app(\App\Models\Tenant::class)->newQuery()->find($tenantId);
        if (!$tenant) {
            $this->error("Tenant with ID {$tenantId} not found");
            return 1;
        }
        
        $this->info("Running safe migrations for tenant: {$tenantId}");
        
        try {
            $tenant->run(function () use ($tenantId, $tenant) {
                // Check if migrations table exists
                if (!Schema::hasTable('migrations')) {
                    $this->info('Creating migrations table');
                    
                    try {
                        Schema::create('migrations', function ($table) {
                            $table->increments('id');
                            $table->string('migration');
                            $table->integer('batch');
                        });
                        
                        $this->info('Migrations table created successfully');
                    } catch (\Exception $e) {
                        // If the table already exists, just log it and continue
                        $this->warn('Error creating migrations table: ' . $e->getMessage());
                        Log::warning('Error creating migrations table: ' . $e->getMessage());
                        
                        // Check if we need to manually fix the migrations table
                        if (strpos($e->getMessage(), "already exists") !== false) {
                            $this->warn('Table migrations already exists, continuing with existing table');
                        } else {
                            throw $e; // Re-throw unexpected errors
                        }
                    }
                }
                
                // Get list of applied migrations from the database
                $appliedMigrations = [];
                try {
                    $appliedMigrations = DB::table('migrations')->pluck('migration')->toArray();
                    $this->info('Found ' . count($appliedMigrations) . ' already applied migrations');
                } catch (\Exception $e) {
                    $this->warn('Error fetching applied migrations: ' . $e->getMessage());
                    Log::warning('Error fetching applied migrations: ' . $e->getMessage());
                }
                
                // Run migrations, checking for table existence conflicts
                try {
                    // First, run the migrations in pretend mode to see what would happen
                    $this->info('Running migrations in pretend mode to check for conflicts');
                    $output = '';
                    
                    // First run the general migrations if no path specified
                    if (!$this->option('path')) {
                        \Illuminate\Support\Facades\Artisan::call('migrate', [
                            '--force' => $this->option('force'),
                            '--pretend' => true,
                        ]);
                        
                        $output = \Illuminate\Support\Facades\Artisan::output();
                        $this->info($output);
                    }
                    
                    // Always run tenant-specific migrations
                    \Illuminate\Support\Facades\Artisan::call('migrate', [
                        '--force' => $this->option('force'),
                        '--path' => $this->option('path') ?: 'database/migrations/tenant',
                        '--pretend' => true,
                    ]);
                    
                    $output = \Illuminate\Support\Facades\Artisan::output();
                    
                    if (strpos($output, 'Nothing to migrate') !== false && !$this->option('path')) {
                        $this->info('Nothing to migrate');
                        return 0;
                    }
                    
                    // Now run the migrations for real
                    $this->info('Running migrations');
                    
                    // First run the general migrations if no path specified
                    if (!$this->option('path')) {
                        \Illuminate\Support\Facades\Artisan::call('migrate', [
                            '--force' => $this->option('force'),
                        ]);
                        
                        $output = \Illuminate\Support\Facades\Artisan::output();
                        $this->info($output);
                    }
                    
                    // Always run tenant-specific migrations
                    \Illuminate\Support\Facades\Artisan::call('migrate', [
                        '--force' => $this->option('force'),
                        '--path' => $this->option('path') ?: 'database/migrations/tenant',
                    ]);
                    
                    $output = \Illuminate\Support\Facades\Artisan::output();
                    $this->info($output);
                    
                    $this->info('Migrations completed successfully');
                    Log::info('Tenant migrations completed successfully', ['tenant_id' => $tenantId]);
                    
                    return 0;
                } catch (\Exception $e) {
                    $this->error('Error running migrations: ' . $e->getMessage());
                    Log::error('Error running tenant migrations', [
                        'tenant_id' => $tenantId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return 1;
                }
            });
        } catch (\Exception $e) {
            $this->error('Error in tenant context: ' . $e->getMessage());
            Log::error('Error in tenant context', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        $this->info('Safe tenant migration completed');
        return 0;
    }
} 