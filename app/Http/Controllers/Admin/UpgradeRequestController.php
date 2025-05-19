<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UpgradeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpgradeRequestController extends Controller
{
    /**
     * Display a listing of the upgrade requests.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pendingRequests = UpgradeRequest::with('tenant', 'admin')
            ->where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->get();
            
        $processedRequests = UpgradeRequest::with('tenant', 'admin')
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('processed_at', 'desc')
            ->limit(10)
            ->get();
            
        return view('central.admin.tenant-requests.upgrade-requests', compact('pendingRequests', 'processedRequests'));
    }
    
    /**
     * Approve an upgrade request.
     *
     * @param  \App\Models\UpgradeRequest  $upgradeRequest
     * @return \Illuminate\Http\Response
     */
    public function approve(UpgradeRequest $upgradeRequest)
    {
        // Check if request is still pending
        if ($upgradeRequest->status !== 'pending') {
            return redirect()->route('admin.upgrade-requests.index')
                ->with('error', 'This request has already been processed.');
        }
        
        // Start a database transaction
        \DB::beginTransaction();
        
        try {
            // Update tenant to premium or free status
            $tenant = $upgradeRequest->tenant;
            
            if ($upgradeRequest->request_type === 'downgrade') {
                $tenant->plan = 'free';
                $action = 'downgraded to Free';
            } else {
                $tenant->plan = 'premium';
                $action = 'upgraded to Premium';
            }
            
            $tenant->save();
            
            // Update the request status
            $upgradeRequest->status = 'approved';
            $upgradeRequest->processed_at = now();
            $upgradeRequest->processed_by = Auth::id();
            $upgradeRequest->notes = 'Approved by admin';
            $upgradeRequest->save();
            
            \DB::commit();
            
            return redirect()->route('admin.upgrade-requests.index')
                ->with('success', "Request for {$tenant->department_name} has been approved. Their plan has been {$action}.");
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return redirect()->route('admin.upgrade-requests.index')
                ->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject an upgrade request.
     *
     * @param  \App\Models\UpgradeRequest  $upgradeRequest
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reject(UpgradeRequest $upgradeRequest, Request $request)
    {
        // Validate rejection reason
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        // Check if request is still pending
        if ($upgradeRequest->status !== 'pending') {
            return redirect()->route('admin.upgrade-requests.index')
                ->with('error', 'This request has already been processed.');
        }
        
        // Update the request status
        $upgradeRequest->status = 'rejected';
        $upgradeRequest->processed_at = now();
        $upgradeRequest->processed_by = Auth::id();
        $upgradeRequest->notes = $validated['rejection_reason'];
        $upgradeRequest->save();
        
        $requestType = $upgradeRequest->request_type === 'downgrade' ? 'downgrade' : 'upgrade';
        
        return redirect()->route('admin.upgrade-requests.index')
            ->with('success', "{$requestType} request for {$upgradeRequest->tenant->department_name} has been rejected.");
    }
} 