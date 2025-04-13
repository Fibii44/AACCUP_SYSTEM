<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TenantController extends Controller
{
    public function index()
    {
        return view('central.admin.tenants');
    }
    
    public function about()
    {
        return view('landing.about');
    }
    
    public function contact()
    {
        return view('landing.contact');
    }
    
    public function enable(Tenant $tenant)
    {
        try {
            $tenant->enableDomain();
            Log::info('Tenant domain enabled', ['tenant_id' => $tenant->id]);
            return redirect()->back()->with('success', 'Tenant domain has been enabled.');
        } catch (\Exception $e) {
            Log::error('Failed to enable tenant domain', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to enable tenant domain.');
        }
    }

    public function disable(Tenant $tenant)
    {
        try {
            $tenant->disableDomain();
            Log::info('Tenant domain disabled', ['tenant_id' => $tenant->id]);
            return redirect()->back()->with('success', 'Tenant domain has been disabled.');
        } catch (\Exception $e) {
            Log::error('Failed to disable tenant domain', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to disable tenant domain.');
        }
    }

    public function upgrade(Tenant $tenant)
    {
        try {
            $tenant->upgradeToPremium();
            Log::info('Tenant upgraded to premium', ['tenant_id' => $tenant->id]);
            return redirect()->back()->with('success', 'Tenant has been upgraded to premium plan.');
        } catch (\Exception $e) {
            Log::error('Failed to upgrade tenant to premium', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to upgrade tenant to premium plan.');
        }
    }

    public function downgrade(Tenant $tenant)
    {
        try {
            $tenant->downgradeToFree();
            Log::info('Tenant downgraded to free', ['tenant_id' => $tenant->id]);
            return redirect()->back()->with('success', 'Tenant has been downgraded to free plan.');
        } catch (\Exception $e) {
            Log::error('Failed to downgrade tenant to free', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to downgrade tenant to free plan.');
        }
    }
} 