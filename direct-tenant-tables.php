<?php
// direct-tenant-tables.php - Directly create tables in tenant database

// Load Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

// Get tenant ID from command line
$tenantId = $argv[1] ?? null;

if (!$tenantId) {
    echo "Usage: php direct-tenant-tables.php <tenant_id>\n";
    exit(1);
}

echo "Creating tables for tenant: $tenantId\n";

// Get database name for this tenant
$dbName = 'tenant_' . $tenantId;

// Check if database exists
$dbExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);
if (empty($dbExists)) {
    echo "ERROR: Database does not exist: $dbName\n";
    
    // Create it
    try {
        DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");
        echo "Database created: $dbName\n";
    } catch (\Exception $e) {
        echo "Failed to create database: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "Database exists: $dbName\n";
}

// Get DB config from Laravel
$config = config('database.connections.mysql');

// Create tables using direct SQL
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    
    'tenant_settings' => "CREATE TABLE IF NOT EXISTS `tenant_settings` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `primary_color` varchar(255) NOT NULL DEFAULT '#3490dc',
        `secondary_color` varchar(255) NOT NULL DEFAULT '#6c757d',
        `logo_url` varchar(255) DEFAULT NULL,
        `header_text` varchar(255) NOT NULL DEFAULT 'Welcome to Our Platform',
        `welcome_message` text DEFAULT NULL,
        `show_testimonials` tinyint(1) NOT NULL DEFAULT 1,
        `footer_text` text DEFAULT NULL,
        `custom_css` json DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

// Use PHP's mysqli to connect directly to the tenant database
$mysqli = new mysqli(
    $config['host'], 
    $config['username'], 
    $config['password'], 
    $dbName,
    $config['port'] ?? 3306
);

if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error . "\n";
    exit(1);
}

echo "Connected to database $dbName\n";

// Create tables
foreach ($tables as $table => $sql) {
    echo "Creating table $table...\n";
    if ($mysqli->query($sql) === TRUE) {
        echo "Table $table created successfully\n";
    } else {
        echo "Error creating table $table: " . $mysqli->error . "\n";
    }
}

// Insert admin user if users table is empty
$result = $mysqli->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo "Creating admin user...\n";
    
    $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
    $now = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO users (name, email, password, role, created_at, updated_at) 
            VALUES ('Admin User', 'admin@{$tenantId}.test', '$hashedPassword', 'admin', '$now', '$now')";
    
    if ($mysqli->query($sql) === TRUE) {
        echo "Admin user created successfully\n";
    } else {
        echo "Error creating admin user: " . $mysqli->error . "\n";
    }
}

// Create default landing page settings if tenant_settings table is empty
$result = $mysqli->query("SELECT COUNT(*) as count FROM tenant_settings");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo "Creating default landing page settings...\n";
    
    $now = date('Y-m-d H:i:s');
    $headerText = 'Welcome to ' . ucfirst($tenantId) . ' Portal';
    $footerText = '© ' . date('Y') . ' ' . ucfirst($tenantId) . ' Department';
    
    $sql = "INSERT INTO tenant_settings (
                primary_color, 
                secondary_color, 
                header_text, 
                welcome_message,
                show_testimonials,
                footer_text,
                created_at, 
                updated_at
            ) VALUES (
                '#3490dc', 
                '#6c757d', 
                '$headerText', 
                'Welcome to your customized department portal. Login to access your resources.',
                1,
                '$footerText',
                '$now', 
                '$now'
            )";
    
    if ($mysqli->query($sql) === TRUE) {
        echo "Default landing page settings created successfully\n";
    } else {
        echo "Error creating landing page settings: " . $mysqli->error . "\n";
    }
}

// Show all tables in the database
$result = $mysqli->query("SHOW TABLES");
echo "\nTables in database $dbName:\n";
while ($row = $result->fetch_row()) {
    echo "- " . $row[0] . "\n";
}

// Close connection
$mysqli->close();

echo "\nAll tables created successfully in $dbName\n"; 