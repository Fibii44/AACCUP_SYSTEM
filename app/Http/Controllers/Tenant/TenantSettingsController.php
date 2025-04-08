<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantSetting;
use Illuminate\Http\Request;

class TenantSettingsController extends Controller
{
    public function index()
    {
        // Get the current settings from the database
        $settings = TenantSetting::first();
        
        // Debug the current colors
        \Log::info('Current Settings:', [
            'primary_color' => $settings ? $settings->primary_color : 'No settings found',
            'secondary_color' => $settings ? $settings->secondary_color : 'No settings found'
        ]);
        
        return view('tenant.settings', compact('settings'));
    }

    public function update(Request $request) 
    {
        $settings = TenantSetting::firstOrNew();
        
        // Update all settings
        $settings->fill($request->only([
            'logo_url',
            'primary_color',
            'secondary_color',
            'header_text',
            'welcome_message',
            'footer_text'
        ]));
        
        $settings->save();
        
        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}