<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\UpgradeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SubscriptionController extends Controller
{
    /**
     * Display the subscription plans page
     */
    public function index()
    {
        // Get the current tenant
        $tenant = tenant();
        
        // Check for pending requests in the central database
        $hasPendingRequest = false;
        
        try {
            // Connect to central database to get pending requests
            $pendingRequest = DB::connection('mysql')->table('upgrade_requests')
                ->where('tenant_id', $tenant->id)
                ->where('status', 'pending')
                ->first();
            
            $hasPendingRequest = !empty($pendingRequest);
        } catch (\Exception $e) {
            // Log the error but continue - don't break the page
            Log::error('Error checking for pending upgrade requests', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return view('tenant.subscription', [
            'tenant' => $tenant,
            'isPremium' => $tenant->isPremium(),
            'hasPendingRequest' => $hasPendingRequest
        ]);
    }
    
    /**
     * Request an upgrade to premium plan (requires admin approval)
     */
    public function upgrade()
    {
        try {
            // Get the current tenant
            $tenant = tenant();
            
            // Check if upgrade is needed
            if ($tenant->isPremium()) {
                return redirect()->route('tenant.subscription')
                    ->with('info', 'Your subscription is already Premium.');
            }
            
            // Check if there's already a pending request in the central database
            $pendingRequest = DB::connection('mysql')->table('upgrade_requests')
                ->where('tenant_id', $tenant->id)
                ->where('status', 'pending')
                ->first();
                
            if ($pendingRequest) {
                return redirect()->route('tenant.subscription')
                    ->with('info', 'Your upgrade request is already pending approval.');
            }
            
            // Create upgrade request in the central database
            DB::connection('mysql')->table('upgrade_requests')->insert([
                'tenant_id' => $tenant->id,
                'status' => 'pending',
                'request_type' => 'upgrade',
                'requested_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('Tenant upgrade request submitted', ['tenant_id' => $tenant->id]);
            
            return redirect()->route('tenant.subscription')
                ->with('success', 'Your upgrade request has been submitted and is pending approval.');
        } catch (\Exception $e) {
            Log::error('Failed to request tenant upgrade', [
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('tenant.subscription')
                ->with('error', 'Failed to submit upgrade request. Please try again.');
        }
    }
    
    /**
     * Request a downgrade to free plan (requires admin approval)
     */
    public function downgrade()
    {
        try {
            // Get the current tenant
            $tenant = tenant();
            
            // Check if downgrade is needed
            if (!$tenant->isPremium()) {
                return redirect()->route('tenant.subscription')
                    ->with('info', 'Your subscription is already Free.');
            }
            
            // Check if there's already a pending request in the central database
            $pendingRequest = DB::connection('mysql')->table('upgrade_requests')
                ->where('tenant_id', $tenant->id)
                ->where('status', 'pending')
                ->first();
                
            if ($pendingRequest) {
                return redirect()->route('tenant.subscription')
                    ->with('info', 'Your plan change request is already pending approval.');
            }
            
            // Create downgrade request in the central database
            DB::connection('mysql')->table('upgrade_requests')->insert([
                'tenant_id' => $tenant->id,
                'status' => 'pending',
                'request_type' => 'downgrade',
                'requested_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('Tenant downgrade request submitted', ['tenant_id' => $tenant->id]);
            
            return redirect()->route('tenant.subscription')
                ->with('success', 'Your downgrade request has been submitted and is pending approval.');
        } catch (\Exception $e) {
            Log::error('Failed to request tenant downgrade', [
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('tenant.subscription')
                ->with('error', 'Failed to submit downgrade request. Please try again.');
        }
    }
} 