<?php

use App\Http\Controllers\Admin\TenantApprovalController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\TenantRequestController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Central domain routes
Route::domain(config('app.url'))->group(function () {
    // Landing pages
    Route::get('/', [LandingController::class, 'index'])->name('landing.index');
    Route::get('/about', [LandingController::class, 'about'])->name('landing.about');
    Route::get('/contact', [LandingController::class, 'contact'])->name('landing.contact');
    
    // Tenant registration process
    Route::get('/register-department', [TenantRequestController::class, 'create'])->name('tenant.request');
    Route::post('/register-department', [TenantRequestController::class, 'store'])->name('tenant.request.store');
    Route::get('/register-department/success', [TenantRequestController::class, 'success'])->name('tenant.request.success');
    
    // Include Laravel's default auth routes
    require __DIR__.'/auth.php';
    
    // Admin dashboard routes
    Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
        
        // Tenant approval routes
        Route::get('/tenant-requests', [TenantApprovalController::class, 'index'])->name('admin.tenant-requests.index');
        Route::get('/tenant-requests/{tenantRequest}', [TenantApprovalController::class, 'show'])->name('admin.tenant-requests.show');
        Route::post('/tenant-requests/{tenantRequest}/approve', [TenantApprovalController::class, 'approve'])->name('admin.tenant-requests.approve');
        Route::post('/tenant-requests/{tenantRequest}/reject', [TenantApprovalController::class, 'reject'])->name('admin.tenant-requests.reject');
        
        // Admin settings routes
        Route::redirect('settings', 'settings/profile');
        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
        Volt::route('settings/password', 'settings.password')->name('settings.password');
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
        
        // Landing page settings
        Route::get('/landing-settings', [LandingPageController::class, 'index'])->name('admin.landing-settings');
        Route::post('/landing-settings', [LandingPageController::class, 'updateSettings'])->name('admin.landing-settings.update');
    });
});

// This line should be removed or commented out since we've moved it inside the domain group
// require __DIR__.'/auth.php';
