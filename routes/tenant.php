<?php

declare(strict_types=1);

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Tenant\TenantLoginController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Landing page for tenants (customizable)
    Route::get('/', [LandingPageController::class, 'index'])->name('landing');
    
    // Tenant Authentication Routes (custom implementation)
    Route::get('/login', [TenantLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [TenantLoginController::class, 'login'])->name('tenant.login');
    Route::post('/logout', [TenantLoginController::class, 'logout'])->name('tenant.logout');
    
    // Authenticated tenant routes
    Route::middleware(['auth'])->group(function () {
        // Admin can customize the landing page
        Route::get('/admin/landing-settings', [LandingPageController::class, 'index'])->name('tenant.landing-settings');
        Route::post('/admin/landing-settings', [LandingPageController::class, 'updateSettings'])->name('tenant.landing-settings.update');
        
        // Add a dashboard for tenant users
        Route::get('/dashboard', function () {
            return view('tenant.dashboard');
        })->name('tenant.dashboard');
    });
});
