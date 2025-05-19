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
    
    // Get the current user
    $user = auth()->user();
    
    // Get user's recent uploads
    $recentUploads = \App\Models\Upload::where('user_id', $user->id)
                    ->with(['indicator'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
    
    // Get user's assigned areas
    $assignedAreas = \App\Models\Area::whereHas('parameters.indicators.uploads', function($query) use ($user) {
        $query->where('user_id', $user->id);
    })->get();

    // Get the first instrument
    $instrument = \App\Models\Instrument::first();

    // Get the first assigned area for parameters
    $firstArea = $assignedAreas->first();
@endphp

<style>
    /* Custom styling based on tenant settings */
    .card {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        border-radius: 10px;
        border: none;
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .card .numbers p {
        color: {{ $tertiaryColor }};
        font-weight: 600;
    }

    .card .numbers h5 {
        color: {{ $primaryColor }};
        font-weight: 700;
    }

    .bg-gradient-primary {
        background: {{ $primaryColor }};
    }

    .text-primary {
        color: {{ $primaryColor }} !important;
    }

    .card-header h6 {
        color: {{ $tertiaryColor }};
        font-weight: 600;
    }

    .timeline-step {
        background: {{ $primaryColor }};
    }

    .table thead th {
        color: {{ $tertiaryColor }};
        font-weight: 600;
    }

    .progress-bar {
        background-color: {{ $primaryColor }};
    }

    /* Mask colors */
    .mask.bg-primary {
        background: white !important;
    }

    .text-success {
        color: {{ $secondaryColor }} !important;
    }

    .text-white {
        color: #ffffff !important;
    }

    /* Timeline and other elements */
    .timeline-block .timeline-step i {
        color: #ffffff;
    }

    .table .text-secondary {
        color: {{ $tertiaryColor }} !important;
    }

    /* Button styling */
    .btn {
        font-weight: 500;
        border-radius: 6px;
    }

    .btn.bg-gradient-primary {
        background: {{ $primaryColor }} !important;
        background-image: none !important;
    }

    .btn.bg-gradient-info {
        background: {{ adjustBrightness($primaryColor, 20) }} !important;
        background-image: none !important;
    }

    .btn.bg-gradient-success {
        background: {{ adjustBrightness($primaryColor, 40) }} !important;
        background-image: none !important;
    }
</style>

<div class="row">
    <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card">
            <span class="mask bg-primary opacity-10 border-radius-lg"></span>
            <div class="card-body p-3 position-relative">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">My Uploads</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $recentUploads->count() }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="fas fa-upload text-lg text-white" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card">
            <span class="mask bg-primary opacity-10 border-radius-lg"></span>
            <div class="card-body p-3 position-relative">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Assigned Areas</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $assignedAreas->count() }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="fas fa-map-marker-alt text-lg text-white" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-7 mb-lg-0 mb-4">
        <div class="card">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between">
                    <h6 class="mb-0">Recent Uploads</h6>
                    @if($instrument)
                        <a href="{{ route('tenant.areas.index', ['instrument' => $instrument->id]) }}" class="btn btn-sm bg-gradient-primary">View All Areas</a>
                    @endif
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">File Name</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Indicator</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Size</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentUploads as $upload)
                            <tr>
                                <td>
                                    <div class="d-flex px-3 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $upload->file_name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-sm font-weight-bold mb-0">{{ $upload->indicator->name ?? 'N/A' }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <p class="text-sm font-weight-bold mb-0">{{ number_format($upload->size / 1024, 2) }} KB</p>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-sm font-weight-bold">{{ $upload->created_at->format('M d, Y') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header pb-0">
                <h6>Assigned Areas</h6>
                <p class="text-sm">
                    <i class="fa fa-arrow-up text-success"></i>
                    <span class="font-weight-bold">Areas you are responsible for</span>
                </p>
            </div>
            <div class="card-body p-3">
                <div class="timeline timeline-one-side">
                    @foreach($assignedAreas as $area)
                    <div class="timeline-block mb-3">
                        <span class="timeline-step">
                            <i class="ni ni-app text-primary text-gradient"></i>
                        </span>
                        <div class="timeline-content">
                            <h6 class="text-dark text-sm font-weight-bold mb-0">{{ $area->name }}</h6>
                            <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                {{ $area->parameters->count() }} Parameters
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection 