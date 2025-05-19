<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class TenantLoginController extends Controller
{
    /**
     * Show the tenant login form
     */
    public function showLoginForm()
    {
        // Get tenant settings for customization
        $settings = $this->getTenantSettings();
        
        return view('tenant.auth.login', [
            'settings' => $settings
        ]);
    }
    
    /**
     * Handle a login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        // Debug: Check if tenant is initialized
        Log::info('Login attempt - Tenant context check', [
            'is_tenant_initialized' => tenant() ? 'Yes' : 'No',
            'tenant_id' => tenant() ? tenant()->id : 'None',
            'email' => $request->email,
            'domain' => $request->getHost()
        ]);
        
        // Debug: Check if user exists in tenant database
        try {
            $user = \App\Models\User::where('email', $request->email)->first();
            Log::info('User lookup result', [
                'email' => $request->email,
                'user_found' => $user ? 'Yes' : 'No',
                'user_role' => $user ? $user->role : 'N/A'
            ]);
        } catch (\Exception $e) {
            Log::error('Error looking up user', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
        }
        
        // Attempt to log in using tenant guard
        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Get the authenticated user
            $user = Auth::user();
            
            // Check if user is inactive/archived
            if ($user->status === 'inactive') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => ['Your account has been archived. Please contact your administrator for assistance.'],
                ]);
            }
            
            // Log successful login
            Log::info('Tenant user logged in successfully', [
                'email' => $request->email,
                'domain' => $request->getHost()
            ]);
            
            // Redirect based on user role
            if ($user->role === 'user') {
                return redirect()->intended(route('tenant.facultyDashboard'));
            }
            
            // Default redirect for other roles
            return redirect()->intended(route('tenant.dashboard'));
        }
        
        // Log failed login attempt
        Log::warning('Failed tenant login attempt', [
            'email' => $request->email,
            'domain' => $request->getHost(),
            'password_provided' => strlen($request->password) > 0 ? 'Yes' : 'No'
        ]);
        
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }
    
    /**
     * Log the user out
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
    
    /**
     * Get tenant settings or default settings
     */
    private function getTenantSettings()
    {
        try {
            // Try to get tenant settings from database
            $settings = \App\Models\TenantSetting::first();
            
            if (!$settings) {
                // Create default settings if none exist
                $settings = new \App\Models\TenantSetting();
                $settings->primary_color = '#3490dc';
                $settings->secondary_color = '#6c757d';
                $settings->header_text = 'Welcome to ' . tenant('id');
                $settings->save();
            }
            
            return $settings;
            
        } catch (\Exception $e) {
            // If we can't access the tenant DB, create a default object
            Log::error('Error getting tenant settings: ' . $e->getMessage());
            
            $settings = new \stdClass();
            $settings->primary_color = '#3490dc';
            $settings->secondary_color = '#6c757d';
            $settings->logo_url = null;
            $settings->header_text = 'Welcome to Tenant Portal';
            $settings->footer_text = '© ' . date('Y') . ' ' . config('app.name');
            
            return $settings;
        }
    }
    
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'consent select_account', 'access_type' => 'offline'])
            ->scopes([
                'openid', 
                'profile', 
                'email',
                'https://www.googleapis.com/auth/drive',
                'https://www.googleapis.com/auth/drive.file',
                'https://www.googleapis.com/auth/drive.metadata.readonly',
                'https://www.googleapis.com/auth/drive.appdata'
            ])
            ->redirect();
    }
    
    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            Log::info('Google login attempt', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'domain' => request()->getHost()
            ]);
            
            // Check if user exists in the tenant's database
            $user = \App\Models\User::where('email', $googleUser->getEmail())->first();
            
            if (!$user) {
                Log::warning('Google login failed - User not found in tenant database', [
                    'email' => $googleUser->getEmail()
                ]);
                
                return redirect()->route('login')->withErrors([
                    'email' => ['No account found with this Google email. Please contact your administrator.']
                ]);
            }
            
            // Check if user is inactive/archived
            if ($user->status === 'inactive') {
                Log::warning('Google login failed - User is inactive', [
                    'email' => $googleUser->getEmail()
                ]);
                
                return redirect()->route('login')->withErrors([
                    'email' => ['Your account has been archived. Please contact your administrator for assistance.']
                ]);
            }
            
            // Store Google tokens
            $user->update([
                'google_id' => $googleUser->getId(),
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'google_token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                'profile_picture' => $googleUser->getAvatar(),
            ]);
            
            // Login the user
            Auth::login($user);
            request()->session()->regenerate();
            
            Log::info('Tenant user logged in successfully via Google', [
                'email' => $user->email,
                'domain' => request()->getHost(),
                'tokens_stored' => !empty($googleUser->token)
            ]);
            
            // Redirect based on user role
            if ($user->role === 'user') {
                return redirect()->intended(route('tenant.facultyDashboard'));
            }
            
            // Default redirect for other roles
            return redirect()->intended(route('tenant.dashboard'));
            
        } catch (\Exception $e) {
            Log::error('Google login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')->withErrors([
                'email' => ['An error occurred during Google login. Please try again.']
            ]);
        }
    }
}
