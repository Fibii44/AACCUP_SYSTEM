<?php

namespace App\Http\Controllers;

use App\Models\TenantRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TenantRequestController extends Controller
{
    public function create()
    {
        return view('tenant.request');
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenant_requests,email',
            'domain' => 'required|string|unique:tenant_requests,domain|regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
        ], [
            'domain.regex' => 'The domain must contain only lowercase letters, numbers, and hyphens, and cannot start or end with a hyphen.',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        TenantRequest::create([
            'department_name' => $request->department_name,
            'email' => $request->email,
            'domain' => $request->domain,
            'status' => 'pending',
        ]);
        
        return redirect()->route('tenant.request.success');
    }
    
    public function success()
    {
        return view('tenant.success');
    }
}
