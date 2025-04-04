<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TenantRequest;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "Password testing utility\n";
echo "========================\n\n";

// Get latest approved tenant request
$tenantRequest = TenantRequest::where('status', 'approved')
    ->latest()
    ->first();

if (!$tenantRequest) {
    echo "No approved tenant requests found.\n";
    exit(1);
}

echo "Testing tenant: {$tenantRequest->domain}\n";
echo "Email: {$tenantRequest->email}\n";
echo "Password in TenantRequest: " . ($tenantRequest->password ? "EXISTS (" . strlen($tenantRequest->password) . " chars)" : "MISSING") . "\n";

// Check tenant database
$tenant = Tenant::find($tenantRequest->domain);
if (!$tenant) {
    echo "Tenant not found in database.\n";
    exit(1);
}

// Check if tenant contains user with matching email
$userData = null;
$tenant->run(function () use ($tenantRequest, &$userData) {
    $userData = DB::table('users')
        ->where('email', $tenantRequest->email)
        ->first();
});

if (!$userData) {
    echo "User not found in tenant database.\n";
    exit(1);
}

echo "User found in tenant database:\n";
echo "User ID: {$userData->id}\n";
echo "Name: {$userData->name}\n";
echo "Role: " . ($userData->role ?? 'N/A') . "\n";

// Let's try the password
if ($tenantRequest->password) {
    $passwordWorks = false;
    $tenant->run(function () use ($tenantRequest, &$passwordWorks) {
        $user = DB::table('users')
            ->where('email', $tenantRequest->email)
            ->first();
        
        $passwordWorks = Hash::check($tenantRequest->password, $user->password);
    });
    
    echo "\nPassword verification:\n";
    echo "Password from email would work: " . ($passwordWorks ? "YES" : "NO") . "\n";
    
    if (!$passwordWorks) {
        echo "\nWARNING: The password stored in TenantRequest would NOT work for authentication!\n";
        echo "This confirms there's a mismatch between what's sent in email and what's used for login.\n";
    } else {
        echo "\nGood news! The password in TenantRequest MATCHES what would work for authentication.\n";
        echo "If the email is delivered correctly, users should be able to login with that password.\n";
    }
    
    echo "\nPassword details for debugging:\n";
    echo "Stored password: {$tenantRequest->password}\n";
    
    // Fix option
    echo "\nWould you like to fix this tenant's password? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    if ($line === 'y') {
        // Update the database
        $tenant->run(function () use ($tenantRequest) {
            DB::table('users')
                ->where('email', $tenantRequest->email)
                ->update([
                    'password' => Hash::make($tenantRequest->password),
                    'updated_at' => now()
                ]);
        });
        
        echo "Password has been updated to match what's in the email.\n";
    }
}

echo "\nDone!\n"; 