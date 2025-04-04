<?php
// check-tenant-domain.php - Check if a tenant domain exists and is properly set up

// Load Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

// Get tenant ID from command line or use test value
$tenantId = $argv[1] ?? 'itdept';

echo "Checking tenant domain for tenant: $tenantId\n";

// Check if tenant exists
$tenant = Tenant::find($tenantId);

if (!$tenant) {
    echo "ERROR: Tenant does not exist: $tenantId\n";
    
    // Show list of available tenants
    $tenants = Tenant::all();
    if ($tenants->count() > 0) {
        echo "\nAvailable tenants:\n";
        foreach ($tenants as $t) {
            echo "- " . $t->id . "\n";
        }
    } else {
        echo "No tenants found in the system.\n";
    }
    
    exit(1);
}

echo "Found tenant: $tenantId\n";

// Check tenant domains
$domains = $tenant->domains;

if ($domains->isEmpty()) {
    echo "ERROR: No domains found for tenant $tenantId\n";
    
    // Create a domain
    $domain = new Domain();
    $domain->domain = $tenantId . '.aaccup.test';
    $tenant->domains()->save($domain);
    
    echo "Created domain: " . $domain->domain . " for tenant $tenantId\n";
} else {
    echo "Domains for tenant $tenantId:\n";
    foreach ($domains as $domain) {
        echo "- " . $domain->domain . "\n";
    }
}

// Check tenant database
$dbName = 'tenant_' . $tenantId;
$dbExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);

if (empty($dbExists)) {
    echo "ERROR: Database does not exist: $dbName\n";
    exit(1);
} else {
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
    
    // Show all tables in the database
    $result = $mysqli->query("SHOW TABLES");
    echo "\nTables in database $dbName:\n";
    $tableCount = 0;
    
    while ($row = $result->fetch_row()) {
        echo "- " . $row[0] . "\n";
        $tableCount++;
    }
    
    // Close connection
    $mysqli->close();
    
    if ($tableCount === 0) {
        echo "WARNING: No tables found in the tenant database. You should run migrations.\n";
    }
}

// Check if tenant domain resolves locally
$domain = $tenantId . '.aaccup.test';
echo "\nChecking if domain $domain resolves locally...\n";

$resolved = gethostbyname($domain);
if ($resolved === $domain) {
    echo "WARNING: Domain $domain does not resolve. You need to add it to your hosts file.\n";
    echo "Run: php add-tenant-host.php\n";
} else {
    echo "Domain $domain resolves to IP: $resolved\n";
}

echo "\nDONE: Tenant $tenantId is properly set up.\n";
echo "To access the tenant website, visit: http://$domain\n"; 