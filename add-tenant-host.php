<?php

// Load Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TenantRequest;
use Illuminate\Support\Facades\Log;

// Get the approved tenant requests
$approvedTenants = TenantRequest::where('status', 'approved')->get();

if ($approvedTenants->isEmpty()) {
    echo "No approved tenants found.\n";
    exit;
}

echo "Found " . $approvedTenants->count() . " approved tenants.\n";

// Windows hosts file path
$hostsFile = 'C:\Windows\System32\drivers\etc\hosts';

// Check if file exists and is readable
if (!file_exists($hostsFile)) {
    echo "Hosts file not found at: $hostsFile\n";
    exit;
}

// Read current hosts file
$hostsContent = file_get_contents($hostsFile);
if ($hostsContent === false) {
    echo "Could not read hosts file. Make sure you have permission to read: $hostsFile\n";
    exit;
}

// Find hosts entries to add
$newEntries = [];
$existingEntries = [];

foreach ($approvedTenants as $tenant) {
    $domain = $tenant->domain . '.' . config('app.domain');
    
    // Check if domain already exists in hosts file
    if (strpos($hostsContent, $domain) !== false) {
        $existingEntries[] = $domain;
        continue;
    }
    
    $newEntries[] = "127.0.0.1 $domain";
}

// Print existing entries
if (!empty($existingEntries)) {
    echo "The following domains are already in your hosts file:\n";
    foreach ($existingEntries as $domain) {
        echo "- $domain\n";
    }
    echo "\n";
}

// If no new entries, exit
if (empty($newEntries)) {
    echo "No new domains to add.\n";
    exit;
}

// Create the batch file content for adding entries
$batchContent = "@echo off\n";
$batchContent .= "echo Adding tenant domains to hosts file...\n";
$batchContent .= "echo.\n\n";

foreach ($newEntries as $entry) {
    $batchContent .= "echo $entry >> $hostsFile\n";
}

$batchContent .= "echo.\n";
$batchContent .= "echo Domains added successfully!\n";
$batchContent .= "pause\n";

// Write the batch file
file_put_contents('add-tenant-hosts.bat', $batchContent);

echo "The following domains need to be added to your hosts file:\n";
foreach ($newEntries as $entry) {
    echo "- " . str_replace("127.0.0.1 ", "", $entry) . "\n";
}

echo "\nA batch file 'add-tenant-hosts.bat' has been created.\n";
echo "Please run it as Administrator to update your hosts file.\n";
echo "After adding the hosts entries, you may need to restart Laravel Herd or your browser.\n"; 