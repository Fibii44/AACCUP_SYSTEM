@extends('layouts.dashboardTemplate')

@section('content')
@php
    // Get the tenant settings
    $settings = \App\Models\TenantSetting::first() ?? new \App\Models\TenantSetting();
    
    // Get the colors with default fallbacks
    $primaryColor = $settings->primary_color ?? '#3490dc';
    $secondaryColor = $settings->secondary_color ?? '#6c757d';
    $tertiaryColor = $settings->tertiary_color ?? '#1a237e';

    // Determine active tab
    $activeTab = session('active_tab', 'profile');
@endphp

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Profile Information</h6>
                </div>
                <div class="card-body">
                    <!-- Profile Tabs -->
                    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'profile' ? 'active' : '' }}" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}">
                                <i class="fas fa-user me-2"></i>Profile
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'password' ? 'active' : '' }}" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="{{ $activeTab === 'password' ? 'true' : 'false' }}">
                                <i class="fas fa-lock me-2"></i>Update Password
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content mt-4" id="profileTabsContent">
                        <!-- Profile Tab -->
                        <div class="tab-pane fade {{ $activeTab === 'profile' ? 'show active' : '' }}" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            @include('tenant.profile.partials.edit')
                        </div>
                        
                        <!-- Password Tab -->
                        <div class="tab-pane fade {{ $activeTab === 'password' ? 'show active' : '' }}" id="password" role="tabpanel" aria-labelledby="password-tab">
                            @include('tenant.profile.partials.password')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-tabs .nav-link {
        color: {{ $tertiaryColor }};
        border: none;
        padding: 1rem 1.5rem;
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        color: {{ $primaryColor }};
        border-bottom: 2px solid {{ $primaryColor }};
        background: none;
    }

    .nav-tabs .nav-link:hover {
        color: {{ $primaryColor }};
        border-color: transparent;
    }

    .card-header h6 {
        color: {{ $tertiaryColor }};
        font-weight: 600;
    }

    .form-control-label {
        color: {{ $tertiaryColor }};
        font-weight: 600;
    }
</style>
@endsection 