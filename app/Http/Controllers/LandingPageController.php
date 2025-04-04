<?php

namespace App\Http\Controllers;

use App\Models\TenantSetting;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        // Get tenant settings or create default ones if they don't exist
        $settings = TenantSetting::first();
        
        if (!$settings) {
            $settings = TenantSetting::create([
                'primary_color' => '#3490dc',
                'secondary_color' => '#6c757d',
                'header_text' => 'Welcome to ' . config('app.name'),
                'welcome_message' => 'This is your customizable landing page.',
                'show_testimonials' => true,
                'footer_text' => '© ' . date('Y') . ' ' . config('app.name'),
            ]);
        }
        
        return view('landing', ['settings' => $settings]);
    }
    
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'primary_color' => 'required|string|max:255',
            'secondary_color' => 'required|string|max:255',
            'logo_url' => 'nullable|string|max:255',
            'header_text' => 'required|string|max:255',
            'welcome_message' => 'nullable|string',
            'show_testimonials' => 'boolean',
            'footer_text' => 'nullable|string',
            'custom_css' => 'nullable|json',
        ]);
        
        $settings = TenantSetting::first();
        
        if (!$settings) {
            $settings = new TenantSetting();
        }
        
        $settings->fill($validated);
        $settings->save();
        
        return redirect()->back()->with('success', 'Landing page settings updated successfully!');
    }
}
