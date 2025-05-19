@extends('layouts.dashboardTemplate')

@section('content')
@php
    // Get the tenant settings
    $settings = \App\Models\TenantSetting::first() ?? new \App\Models\TenantSetting();
    
    // Get the colors with default fallbacks
    $primaryColor = $settings->primary_color ?? '#3490dc';
    $secondaryColor = $settings->secondary_color ?? '#6c757d';
    $tertiaryColor = $settings->tertiary_color ?? '#1a237e';
    
    // Create a slightly lighter version of primary for hover effects
    $primaryColorLight = $primaryColor . '20'; // Adding 20% opacity

    // Helper function to adjust color brightness
    function adjustBrightness($hex, $steps) {
        // Remove hash if present
        $hex = ltrim($hex, '#');
        
        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Adjust brightness
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        
        // Convert back to hex
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
@endphp

<style>
    .btn-primary {
        background-color: {{ $primaryColor }} !important;
        border-color: {{ $primaryColor }} !important;
    }
    
    .btn-outline-primary {
        color: {{ $primaryColor }} !important;
        border-color: {{ $primaryColor }} !important;
    }
    
    .btn-outline-primary:hover {
        background-color: {{ $primaryColor }} !important;
        color: white !important;
    }
    
    .badge.bg-primary {
        background-color: {{ $primaryColor }} !important;
    }
    
    .border-primary {
        border-color: {{ $primaryColor }} !important;
    }
    
    .text-primary {
        color: {{ $primaryColor }} !important;
    }
    
    .card-header h5 {
        color: {{ $tertiaryColor }};
    }
    
    .table thead th {
        color: {{ $tertiaryColor }};
    }
    
    .text-success {
        color: {{ $secondaryColor }} !important;
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Subscription Plans</h5>
                    <p class="text-sm mb-0">Choose the plan that best fits your needs</p>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Status Messages -->
                    @if(session('success'))
                    <div class="alert alert-success mx-4 mt-4">
                        {{ session('success') }}
                    </div>
                    @endif
                    
                    @if(session('error'))
                    <div class="alert alert-danger mx-4 mt-4">
                        {{ session('error') }}
                    </div>
                    @endif
                    
                    @if(session('info'))
                    <div class="alert alert-info mx-4 mt-4">
                        {{ session('info') }}
                    </div>
                    @endif
                    
                    @if($hasPendingRequest ?? false)
                    <div class="alert alert-warning mx-4 mt-4">
                        <strong>Pending Request:</strong> Your request to {{ $isPremium ? 'downgrade to Free' : 'upgrade to Premium' }} is pending admin approval. You'll be notified once it's processed.
                    </div>
                    @endif
                    
                    <div class="row mt-4 px-4">
                        <!-- Free Plan Card -->
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="card h-100 {{ !$isPremium ? 'border border-primary border-2' : '' }}">
                                @if(!$isPremium)
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge bg-primary text-white">Current Plan</span>
                                </div>
                                @endif
                                <div class="card-header text-center pb-0 pt-3">
                                    <h5 class="mb-0">Free Plan</h5>
                                    <p class="text-sm mb-0">Basic features for small departments</p>
                                    <div class="d-flex justify-content-center mt-3 mb-1">
                                        <span class="h1 mb-0">₱0</span>
                                        <span class="text-sm align-self-end mb-1">/month</span>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <ul class="list-group">
                                        <li class="list-group-item border-0 ps-0 pt-0 text-sm d-flex align-items-center">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <span>Basic reporting</span>
                                        </li>
                                        <li class="list-group-item border-0 ps-0 text-sm d-flex align-items-center">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <span>Up to 50 faculty members</span>
                                        </li>            
                                        <li class="list-group-item border-0 ps-0 text-sm d-flex align-items-center">
                                            <i class="fas fa-times text-danger me-2"></i>
                                            <span class="text-muted">Advanced analytics</span>
                                        </li>
                                        <li class="list-group-item border-0 ps-0 text-sm d-flex align-items-center">
                                            <i class="fas fa-times text-danger me-2"></i>
                                            <span class="text-muted">Free color palettes</span>
                                        </li>
                                        
                                    </ul>
                                    <div class="d-flex justify-content-center mt-4">
                                        @if($isPremium)
                                            @if($hasPendingRequest ?? false)
                                                <button type="button" class="btn btn-secondary mb-0" disabled>
                                                    <i class="fas fa-hourglass-half me-1"></i> Approval Pending
                                                </button>
                                            @else
                                                <form action="{{ route('tenant.subscription.downgrade') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary mb-0">Request Downgrade</button>
                                                </form>
                                            @endif
                                        @else
                                            <button type="button" class="btn btn-primary mb-0" disabled>Current Plan</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Premium Plan Card -->
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="card h-100 {{ $isPremium ? 'border border-primary border-2' : '' }}">
                                @if($isPremium)
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge bg-primary text-white">Current Plan</span>
                                </div>
                                @endif
                                <div class="card-header text-center pb-0 pt-3">
                                    <h5 class="mb-0">Premium Plan</h5>
                                    <p class="text-sm mb-0">Advanced features for larger departments</p>
                                    <div class="d-flex justify-content-center mt-3 mb-1">
                                        <span class="h1 mb-0">₱250</span>
                                        <span class="text-sm align-self-end mb-1">/month</span>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <ul class="list-group">
                                        <li class="list-group-item border-0 ps-0 pt-0 text-sm d-flex align-items-center">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <span>Advanced reporting</span>
                                        </li>
                                        <li class="list-group-item border-0 ps-0 text-sm d-flex align-items-center">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <span>Unlimited faculty members (100+)</span>
                                        </li>                 
                                        <li class="list-group-item border-0 ps-0 text-sm d-flex align-items-center">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <span>Premium color palettes</span>
                                        </li>
                                        <li class="list-group-item border-0 ps-0 text-sm d-flex align-items-center">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <span>Premium dashboard layout</span>
                                        </li>                        
                                    </ul>
                                    <div class="d-flex justify-content-center mt-4">
                                        @if(!$isPremium)
                                            @if($hasPendingRequest ?? false)
                                                <button type="button" class="btn btn-secondary mb-0" disabled>
                                                    <i class="fas fa-hourglass-half me-1"></i> Approval Pending
                                                </button>
                                            @else
                                                <form action="{{ route('tenant.subscription.upgrade') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary mb-0">Request Upgrade</button>
                                                </form>
                                            @endif
                                        @else
                                            <button type="button" class="btn btn-primary mb-0" disabled>Current Plan</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Plan Comparison</h5>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Feature</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Free Plan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Premium Plan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">Faculty Members</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Up to 50</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Unlimited (100+)</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">Reports</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Basic</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Advanced</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">Analytics</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Basic</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Advanced</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">Color Palettes</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Basic</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Premium</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">Dashboard Layout</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Standard</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Premium</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">Support</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Email Only</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Priority Support</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 