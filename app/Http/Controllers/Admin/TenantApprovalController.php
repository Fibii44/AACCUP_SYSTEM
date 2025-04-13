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
            $tenant = Tenant::create([
                'id' => $tenantRequest->domain,
                'department_name' => $tenantRequest->department_name,
                'email' => $tenantRequest->email,
            ]);
            
            Log::info('Tenant record created', ['tenant_id' => $tenant->id]);
            
            // Create a domain for the tenant
            Log::info('Creating domain for tenant', ['domain' => $tenantRequest->domain . '.' . config('app.domain')]);
            
            $domain = $tenant->domains()->create([
                'domain' => $tenantRequest->domain . '.' . config('app.domain'),
            ]);
            
            Log::info('Domain created', ['domain_id' => $domain->id]);
            
            // Refresh the tenant request to make sure we have the latest data
            $tenantRequest = $tenantRequest->fresh();
            
            Log::info('Tenant request confirmation', [
                'status' => $tenantRequest->status,
                'password_exists' => !empty($tenantRequest->password),
                'password_length' => strlen($tenantRequest->password ?? '')
            ]);
            
            // Run migrations for the tenant
            try {
                Log::info('Running migrations for tenant', ['tenant_id' => $tenant->id]);
                $tenant->run(function () {
                    // This runs in the tenant context
                    \Illuminate\Support\Facades\Artisan::call('migrate', [
                        '--force' => true,
                    ]);
                    
                    Log::info('Migrations completed successfully');
                });
            } catch (\Exception $e) {
                Log::error('Error running migrations', [
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
                    // This runs in the tenant context
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
}
