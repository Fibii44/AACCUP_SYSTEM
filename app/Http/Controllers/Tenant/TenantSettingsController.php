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
            'secondary_color' => $settings ? $settings->secondary_color : 'No settings found',
            'tertiary_color' => $settings ? $settings->tertiary_color : 'No settings found'
        ]);
        
        return view('tenant.settings', compact('settings'));
    }

    public function update(Request $request) 
    {
        try {
            // Log the incoming request data
            \Log::info('Tenant Settings Update Request:', [
                'primary_color' => $request->input('primary_color'),
                'secondary_color' => $request->input('secondary_color'),
                'tertiary_color' => $request->input('tertiary_color'),
                'palette' => $request->input('palette')
            ]);
            
            // Validate the color inputs
            $request->validate([
                'primary_color' => 'required|string|starts_with:#',
                'secondary_color' => 'required|string|starts_with:#',
                'tertiary_color' => 'required|string|starts_with:#',
            ], [
                'required' => 'The :attribute color is required.',
                'starts_with' => 'The :attribute must be a valid hex color code starting with #.'
            ]);
            
            $settings = TenantSetting::firstOrNew();
            
            // Get the original values
            $original = [
                'primary_color' => $settings->primary_color,
                'secondary_color' => $settings->secondary_color,
                'tertiary_color' => $settings->tertiary_color,
            ];
            
            // Update all settings
            $settings->fill($request->only([
                'logo_url',
                'primary_color',
                'secondary_color',
                'tertiary_color',
                'header_text',
                'welcome_message',
                'footer_text'
            ]));
            
            $settings->save();
            
            // Log the changes
            \Log::info('Tenant Settings Updated:', [
                'before' => $original,
                'after' => [
                    'primary_color' => $settings->primary_color,
                    'secondary_color' => $settings->secondary_color,
                    'tertiary_color' => $settings->tertiary_color,
                ],
                'palette' => $request->input('palette')
            ]);
            
            return redirect()->back()->with('success', "Settings updated successfully!");
        } catch (\Exception $e) {
            \Log::error('Error updating tenant settings:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error saving settings: ' . $e->getMessage())
                ->withInput();
        }
    }
}