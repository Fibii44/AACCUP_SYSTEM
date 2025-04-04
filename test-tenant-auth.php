<?php
// test-tenant-auth.php - Test tenant user authentication

// Load Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

// Get tenant ID from command line or use test value
$tenantId = $argv[1] ?? 'itdept';

echo "Testing tenant authentication for: $tenantId\n";

// Check if tenant exists
$tenant = Tenant::find($tenantId);

if (!$tenant) {
    echo "ERROR: Tenant does not exist: $tenantId\n";
    exit(1);
}

echo "Found tenant: $tenantId\n";

// Check tenant database
$dbName = 'tenant_' . $tenantId;
$dbExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);

if (empty($dbExists)) {
    echo "ERROR: Database does not exist: $dbName\n";
    exit(1);
}

echo "Database exists: $dbName\n";

// Get DB config from Laravel
$config = config('database.connections.mysql');

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

// Check if users table exists
$result = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    echo "ERROR: Users table does not exist in tenant database\n";
    exit(1);
}

// Check for existing users
$result = $mysqli->query("SELECT * FROM users");
echo "Found " . $result->num_rows . " users in tenant database\n";

if ($result->num_rows == 0) {
    echo "Creating test user...\n";
    $password = Hash::make('password');
    $now = date('Y-m-d H:i:s');
    
    // Create a test user
    $sql = "INSERT INTO users (name, email, password, role, created_at, updated_at) 
            VALUES ('Test User', 'test@{$tenantId}.test', '$password', 'admin', '$now', '$now')";
    
    if ($mysqli->query($sql)) {
        echo "Test user created successfully\n";
    } else {
        echo "Error creating test user: " . $mysqli->error . "\n";
    }
}

// Display available users
$result = $mysqli->query("SELECT id, name, email, role FROM users");
echo "\nAvailable users for testing:\n";

while ($row = $result->fetch_assoc()) {
    echo "- {$row['name']} ({$row['email']}), Role: {$row['role']}\n";
}

$mysqli->close();

echo "\nTo test authentication, visit: http://{$tenantId}.aaccup.test/login\n";
echo "Use one of the user emails listed above with password 'password' for test users\n";
echo "For the admin user, use the password generated during tenant approval\n"; 