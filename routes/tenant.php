<?php

declare(strict_types=1);

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Tenant\TenantLoginController;
use App\Http\Controllers\Tenant\TenantFacultyController;
use App\Http\Controllers\Tenant\TenantSettingsController;
use App\Http\Controllers\Tenant\SubscriptionController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Middleware\CheckTenantDomainStatus;
use App\Http\Middleware\CheckUserStatus;




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
    PreventAccessFromCentralDomains::class,
    InitializeTenancyByDomain::class,
    CheckTenantDomainStatus::class,
    CheckUserStatus::class,
])->group(function () {
    Route::get('/', function () {
        return view('tenant.welcome');
    })->name('tenant.welcome');

    // Landing page for tenants (customizable)
    Route::get('/', [LandingPageController::class, 'index'])->name('landing');
    
    // Tenant Authentication Routes (custom implementation)
    Route::get('/login', [TenantLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [TenantLoginController::class, 'login'])->name('tenant.login');
    Route::post('/logout', [TenantLoginController::class, 'logout'])->name('tenant.logout');
    
    // Google OAuth Routes
    Route::get('/login/google', [TenantLoginController::class, 'redirectToGoogle'])->name('tenant.login.google');
    Route::get('/login/google/callback', [TenantLoginController::class, 'handleGoogleCallback'])->name('tenant.login.google.callback');
    
    // Authenticated tenant routes
    Route::middleware(['auth'])->group(function () {
        // Admin routes
        Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
            // Admin Dashboard
            Route::get('/dashboard', function () {
                return view('tenant.dashboard');
            })->name('tenant.dashboard');

            // Admin management routes
            Route::get('/admin/landing-settings', [LandingPageController::class, 'index'])
                ->name('tenant.landing-settings');
            Route::post('/admin/landing-settings', [LandingPageController::class, 'updateSettings'])
                ->name('tenant.landing-settings.update');

            Route::get('/user-table', [TenantFacultyController::class, 'index'])
                ->name('tenant.user-table');
            Route::post('/faculty/store', [TenantFacultyController::class, 'store'])
                ->name('tenant.faculty.store');
            Route::patch('/faculty/{id}', [TenantFacultyController::class, 'update'])
                ->name('tenant.faculty.update');
            Route::patch('/faculty/{id}/status', [TenantFacultyController::class, 'updateStatus'])
                ->name('tenant.faculty.status');

            Route::get('/settings', [TenantSettingsController::class, 'index'])
                ->name('tenant.settings');
            Route::patch('/settings', [TenantSettingsController::class, 'update'])
                ->name('tenant.settings.update');
                
            // Subscription routes
            Route::get('/subscription', [SubscriptionController::class, 'index'])
                ->name('tenant.subscription');
            Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])
                ->name('tenant.subscription.upgrade');
            Route::post('/subscription/downgrade', [SubscriptionController::class, 'downgrade'])
                ->name('tenant.subscription.downgrade');
        });

        // Faculty routes
        Route::middleware([RoleMiddleware::class . ':user'])->group(function () {
            Route::get('/faculty/dashboard', function () {
                return view('tenant.facultyDashboard');
            })->name('tenant.facultyDashboard');
        });
    });
});