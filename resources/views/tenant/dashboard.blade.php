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

    // Get counts
    $totalUsers = \App\Models\User::where('status', 'active')->count();
    $totalInstruments = \App\Models\Instrument::count();
    $totalAreas = \App\Models\Area::count();
    $totalParameters = \App\Models\Parameter::count();
    
    // Get recent uploads
    $recentUploads = \App\Models\Upload::with(['user', 'indicator'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
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

    .icon-shape {
        width: 48px;
        height: 48px;
        background: {{ $primaryColor }};
        display: flex;
        align-items: center;
        justify-content: center;
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

    /* Custom colors for different cards */
    

    .card:nth-child(2) .icon-shape {
        background: {{ $secondaryColor }};
    }

    .card:nth-child(3) .icon-shape {
        background: {{ $tertiaryColor }};
    }

    .card:nth-child(4) .icon-shape {
        background: {{ $primaryColor }};
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
</style>

<div class="row">
    <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card">
            <span class="mask bg-primary opacity-10 border-radius-lg"></span>
            <div class="card-body p-3 position-relative">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Users</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $totalUsers }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="ni ni-circle-08 text-lg opacity-10" aria-hidden="true"></i>
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
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Instruments</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $totalInstruments }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="ni ni-collection text-lg opacity-10" aria-hidden="true"></i>
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
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Areas</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $totalAreas }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="ni ni-app text-lg opacity-10" aria-hidden="true"></i>
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
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Parameters</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $totalParameters }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
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
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">File Name</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Uploaded By</th>
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
                                            <p class="text-xs text-secondary mb-0">{{ $upload->indicator->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-sm font-weight-bold mb-0">{{ $upload->user->name ?? 'N/A' }}</p>
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
                <h6>Areas Overview</h6>
                <p class="text-sm">
                    <i class="fa fa-arrow-up text-success"></i>
                    <span class="font-weight-bold">Areas and their parameters</span>
                </p>
            </div>
            <div class="card-body p-3">
                <div class="timeline timeline-one-side">
                    @foreach(\App\Models\Area::with('parameters')->take(5)->get() as $area)
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

<div class="row mt-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <h6>Parameters Progress</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center justify-content-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Parameter</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Area</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Indicators</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">Uploads</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Parameter::with(['area', 'indicators'])->take(5)->get() as $parameter)
                            <tr>
                                <td>
                                    <div class="d-flex px-2">
                                        <div>
                                            <div class="icon icon-shape icon-sm bg-gradient-primary shadow text-center">
                                                <i class="ni ni-chart-bar-32 text-white opacity-10"></i>
                                            </div>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0 text-sm">{{ $parameter->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-sm font-weight-bold mb-0">{{ $parameter->area->name ?? 'N/A' }}</p>
                                </td>
                                <td>
                                    <span class="text-sm font-weight-bold">{{ $parameter->indicators->count() }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="me-2 text-sm font-weight-bold">
                                            {{ \App\Models\Upload::whereIn('indicator_id', $parameter->indicators->pluck('id'))->count() }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection 