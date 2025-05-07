@extends('layouts.dashboardTemplate')
@section('styles')
<style>
    /* Simple, clean styles */
    .update-card {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }
    
    .version-badge {
        font-size: 2rem;
        font-weight: 600;
    }
    
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    
    .status-indicator.success {
        background-color: #28a745;
    }
    
    .status-indicator.warning {
        background-color: #ffc107;
    }
    
    .status-indicator.danger {
        background-color: #dc3545;
    }
    
    .simple-divider {
        height: 1px;
        background-color: #f0f0f0;
        margin: 1rem 0;
    }
</style>
@endsection

@php
    // Get the tenant settings
    $settings = \App\Models\TenantSetting::first() ?? new \App\Models\TenantSetting();
    
    // Get the colors with default fallbacks
    $primaryColor = $settings->primary_color ?? '#3490dc';
    $secondaryColor = $settings->secondary_color ?? '#6c757d';
    $tertiaryColor = $settings->tertiary_color ?? '#1a237e';
    
    // Define health status levels based on update status
    $healthStatus = $isNewVersionAvailable ? 'warning' : 'excellent';
    $healthStatusText = $isNewVersionAvailable ? 'Update Available' : 'Optimal';
    $healthStatusColor = $isNewVersionAvailable ? 'warning' : 'success';
    $healthIcon = $isNewVersionAvailable ? 'fa-exclamation-triangle' : 'fa-check-circle';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row mt-4">
            <div class="col-12">
                <!-- Simple header with clean design -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0 fw-bold">System Updates</h4>
                    <div>
                        <span class="text-muted small">Last checked: {{ now()->format('M d, Y') }}</span>
                    </div>
                </div>
                
                <!-- Alert messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="row mb-4">
                    <!-- Current version card -->
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="card update-card h-100 border-0">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="mb-0">Current Version</h5>
                                    <span class="badge bg-{{ $isNewVersionAvailable ? 'warning' : 'success' }}">
                                        {{ $isNewVersionAvailable ? 'Update Available' : 'Up to Date' }}
                                    </span>
                                </div>
                                
                                <div class="text-center my-4">
                                    <span class="version-badge text-{{ $isNewVersionAvailable ? 'muted' : 'success' }}">
                                        {{ $currentVersion }}
                                    </span>
                                </div>
                                
                                <div class="d-flex justify-content-center mt-4">
                                    <form action="{{ route('tenant.system-updates.check') }}" method="POST" id="check-form">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary">
                                            Check for Updates
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Update info card -->
                    <div class="col-md-6">
                        <div class="card update-card h-100 border-0">
                            <div class="card-body p-4">
                                <h5 class="mb-4">Update Information</h5>
                                
                                @if($isNewVersionAvailable)
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Available version:</span>
                                            <span class="fw-bold">{{ $latestVersion }}</span>
                                        </div>
                                        <div class="simple-divider"></div>
                                        <p class="text-muted small mb-3">
                                            This update includes bug fixes, security improvements, and feature enhancements.
                                        </p>
                                        <form action="{{ route('tenant.system-updates.update') }}" method="POST" id="update-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100">
                                                Update Now
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="status-indicator success"></div>
                                        <span>Your system is up to date</span>
                                    </div>
                                    <p class="text-muted small">
                                        You have the latest version installed. Check back later for updates.
                                    </p>
                                @endif
                                
                                <div class="mt-3">
                                    <form action="{{ route('tenant.system-updates.rollback') }}" method="POST" id="rollback-form">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            Rollback to Previous Version
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Version history -->
                <div class="card update-card border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Update History</h5>
                        
                        @if(count($updateHistory) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Version</th>
                                            <th>Date</th>
                                            <th>Changes</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($updateHistory as $history)
                                            <tr>
                                                <td class="fw-medium">{{ $history->version }}</td>
                                                <td>{{ $history->created_at->format('M d, Y') }}</td>
                                                <td>{{ $history->changes }}</td>
                                                <td>
                                                    @if($history->status === 'success')
                                                        <span class="text-success">Success</span>
                                                    @elseif($history->status === 'failed')
                                                        <span class="text-danger">Failed</span>
                                                    @elseif($history->status === 'rolled-back')
                                                        <span class="text-warning">Rolled Back</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No update history available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Simple loading state for buttons
        const checkForm = document.getElementById('check-form');
        if (checkForm) {
            checkForm.addEventListener('submit', function() {
                const button = this.querySelector('button');
                button.innerHTML = 'Checking...';
                button.disabled = true;
            });
        }
        
        const updateForm = document.getElementById('update-form');
        if (updateForm) {
            updateForm.addEventListener('submit', function() {
                const button = this.querySelector('button');
                button.innerHTML = 'Updating...';
                button.disabled = true;
            });
        }
        
        const rollbackForm = document.getElementById('rollback-form');
        if (rollbackForm) {
            rollbackForm.addEventListener('submit', function() {
                const button = this.querySelector('button');
                button.innerHTML = 'Rolling back...';
                button.disabled = true;
            });
        }
    });
</script>
@endsection 