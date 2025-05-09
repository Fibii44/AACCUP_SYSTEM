<?php

declare(strict_types=1);

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Tenant\TenantLoginController;
use App\Http\Controllers\Tenant\TenantFacultyController;
use App\Http\Controllers\Tenant\TenantSettingsController;
use App\Http\Controllers\Tenant\SubscriptionController;
use App\Http\Controllers\Tenant\AreaController;
use App\Http\Controllers\Tenant\ParameterController;
use App\Http\Controllers\Tenant\IndicatorController;
use App\Http\Controllers\Tenant\TenantReportController;
use App\Http\Controllers\Tenant\SystemUpdateController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Middleware\CheckTenantDomainStatus;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Controllers\Tenant\InstrumentController;
use App\Http\Controllers\Tenant\UploadController;
use App\Http\Controllers\Tenant\ProfileController;


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
        // Profile routes
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

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
                
            // Reports routes
            Route::get('/reports', [TenantReportController::class, 'index'])
                ->name('tenant.reports');
            Route::get('/reports/generate', [TenantReportController::class, 'generate'])
                ->name('tenant.reports.generate');
            Route::post('/reports/download', [TenantReportController::class, 'download'])
                ->name('tenant.reports.download');
                
            // Subscription routes
            Route::get('/subscription', [SubscriptionController::class, 'index'])
                ->name('tenant.subscription');
            Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])
                ->name('tenant.subscription.upgrade');
            Route::post('/subscription/downgrade', [SubscriptionController::class, 'downgrade'])
                ->name('tenant.subscription.downgrade');
                
            // System Updates routes
            Route::get('/system-updates', [SystemUpdateController::class, 'index'])
                ->name('tenant.system-updates.index');
            Route::post('/system-updates/check', [SystemUpdateController::class, 'check'])
                ->name('tenant.system-updates.check');
            Route::post('/system-updates/update', [SystemUpdateController::class, 'update'])
                ->name('tenant.system-updates.update');
            Route::post('/system-updates/rollback', [SystemUpdateController::class, 'rollback'])
                ->name('tenant.system-updates.rollback');
            Route::get('/system-updates/debug', [SystemUpdateController::class, 'debugVersion'])
                ->name('tenant.system-updates.debug');
        });

        // Faculty routes
        Route::middleware([RoleMiddleware::class . ':user'])->group(function () {
            Route::get('/faculty/dashboard', function () {
                return view('tenant.facultyDashboard');
            })->name('tenant.facultyDashboard');
        });

        // Instrument routes
        Route::get('/instruments', [InstrumentController::class, 'index'])->name('tenant.instruments.index');
        Route::get('/instruments/{instrument}', [InstrumentController::class, 'show'])->name('tenant.instruments.show');
        Route::get('/instruments/{instrument}/{area}', [InstrumentController::class, 'showArea'])->name('tenant.instruments.area.show');
        Route::post('/instruments', [InstrumentController::class, 'store'])->name('tenant.instruments.store');
        Route::put('/instruments/{instrument}', [InstrumentController::class, 'update'])->name('tenant.instruments.update');
        Route::delete('/instruments/{instrument}', [InstrumentController::class, 'destroy'])->name('tenant.instruments.destroy');
        
        // Area routes
        Route::get('/instruments/{instrument}/areas', [AreaController::class, 'index'])->name('tenant.areas.index');
        Route::get('/areas/{area}', [AreaController::class, 'show'])->name('tenant.areas.show');
        Route::post('/instruments/{instrument}/areas', [AreaController::class, 'store'])->name('tenant.areas.store');
        Route::put('/areas/{area}', [AreaController::class, 'update'])->name('tenant.areas.update');
        Route::delete('/areas/{area}', [AreaController::class, 'destroy'])->name('tenant.areas.destroy');
        
        // Parameter routes
        Route::get('/areas/{area}/parameters', [ParameterController::class, 'index'])->name('tenant.parameters.index');
        Route::get('/parameters/{parameter}', [ParameterController::class, 'show'])->name('tenant.parameters.show');
        Route::post('/areas/{area}/parameters', [ParameterController::class, 'store'])->name('tenant.parameters.store');
        Route::put('/parameters/{parameter}', [ParameterController::class, 'update'])->name('tenant.parameters.update');
        Route::delete('/parameters/{parameter}', [ParameterController::class, 'destroy'])->name('tenant.parameters.destroy');
        
        // Indicator routes
        Route::get('/parameters/{parameter}/indicators', [IndicatorController::class, 'index'])->name('tenant.indicators.index');
        Route::get('/indicators/{indicator}', [IndicatorController::class, 'show'])->name('tenant.indicators.show');
        Route::post('/parameters/{parameter}/indicators', [IndicatorController::class, 'store'])->name('tenant.indicators.store');
        Route::put('/indicators/{indicator}', [IndicatorController::class, 'update'])->name('tenant.indicators.update');
        Route::delete('/indicators/{indicator}', [IndicatorController::class, 'destroy'])->name('tenant.indicators.destroy');
        
        // Upload routes
        Route::get('/indicators/{indicator}/uploads', [UploadController::class, 'index'])->name('tenant.uploads.index');
        Route::post('/indicators/{indicator}/uploads', [UploadController::class, 'store'])->name('tenant.uploads.store');
       
    });
});