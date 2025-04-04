<?php

namespace App\Observers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class TenantObserver
{
    /**
     * Handle the Tenant "created" event.
     */
    public function created(Tenant $tenant): void
    {
        Log::info('Tenant created event observed', ['tenant_id' => $tenant->id]);
        
        // Ensure database and tables are created by running our script
        try {
            $this->ensureTenantTablesExist($tenant->id);
        } catch (\Exception $e) {
            Log::error('Error in TenantObserver::created', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Ensure tenant database tables exist
     */
    protected function ensureTenantTablesExist(string $tenantId): void
    {
        Log::info('Ensuring tenant tables exist', ['tenant_id' => $tenantId]);
        
        // Get database name for this tenant
        $dbName = 'tenant_' . $tenantId;
        
        // Get database configuration
        $config = config('database.connections.mysql');
        
        // Create database if it doesn't exist
        try {
            $dbExists = \DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);
            
            if (empty($dbExists)) {
                Log::info('Creating tenant database', ['db_name' => $dbName]);
                \DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");
            }
        } catch (\Exception $e) {
            Log::error('Error checking/creating database', [
                'db_name' => $dbName,
                'error' => $e->getMessage()
            ]);
            return;
        }
        
        // Connect directly to tenant database
        try {
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
                return;
            }
            
            Log::info('Connected to tenant database', ['db_name' => $dbName]);
            
            // Define essential tables
            $tables = [
                'users' => "CREATE TABLE IF NOT EXISTS `users` (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                
                'migrations' => "CREATE TABLE IF NOT EXISTS `migrations` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `migration` varchar(255) NOT NULL,
                    `batch` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                
                'cache' => "CREATE TABLE IF NOT EXISTS `cache` (
                    `key` varchar(255) NOT NULL,
                    `value` mediumtext NOT NULL,
                    `expiration` int(11) NOT NULL,
                    PRIMARY KEY (`key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                
                'cache_locks' => "CREATE TABLE IF NOT EXISTS `cache_locks` (
                    `key` varchar(255) NOT NULL,
                    `owner` varchar(255) NOT NULL,
                    `expiration` int(11) NOT NULL,
                    PRIMARY KEY (`key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                
                'failed_jobs' => "CREATE TABLE IF NOT EXISTS `failed_jobs` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(255) NOT NULL,
                    `connection` text NOT NULL,
                    `queue` text NOT NULL,
                    `payload` longtext NOT NULL,
                    `exception` longtext NOT NULL,
                    `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                
                'jobs' => "CREATE TABLE IF NOT EXISTS `jobs` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `queue` varchar(255) NOT NULL,
                    `payload` longtext NOT NULL,
                    `attempts` tinyint(3) UNSIGNED NOT NULL,
                    `reserved_at` int(10) UNSIGNED DEFAULT NULL,
                    `available_at` int(10) UNSIGNED NOT NULL,
                    `created_at` int(10) UNSIGNED NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `jobs_queue_index` (`queue`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                
                'password_reset_tokens' => "CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
                    `email` varchar(255) NOT NULL,
                    `token` varchar(255) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`email`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
            ];
            
            // Create tables
            foreach ($tables as $table => $sql) {
                try {
                    if ($mysqli->query($sql) === TRUE) {
                        Log::info("Table $table created successfully");
                    } else {
                        Log::error("Error creating table $table", ['error' => $mysqli->error]);
                    }
                } catch (\Exception $e) {
                    Log::error("Exception creating table $table", ['error' => $e->getMessage()]);
                }
            }
            
            // Close database connection
            $mysqli->close();
            
            Log::info('Tenant tables created successfully', ['tenant_id' => $tenantId]);
            
        } catch (\Exception $e) {
            Log::error('Exception during direct database operations', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 