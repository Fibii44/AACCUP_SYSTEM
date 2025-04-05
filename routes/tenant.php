<?php

declare(strict_types=1);

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Tenant\TenantLoginController;
use App\Http\Controllers\Tenant\TenantFacultyController;
use App\Http\Middleware\RoleMiddleware;
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
            Route::delete('/faculty/{id}', [TenantFacultyController::class, 'destroy'])
                ->name('tenant.faculty.destroy');
        });

        // Faculty routes
        Route::middleware([RoleMiddleware::class . ':user'])->group(function () {
            Route::get('/faculty/dashboard', function () {
                return view('tenant.facultyDashboard');
            })->name('tenant.facultyDashboard');
        });
    });
});