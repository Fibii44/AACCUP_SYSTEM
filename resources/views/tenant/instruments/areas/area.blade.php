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
@endphp

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1>{{ $area->name }}</h1>
            
            <div class="card mb-4">
                <div class="card-header" style="background-color: {{ $primaryColor }}; color: white;">
                    Area Details
                </div>
                <div class="card-body">
                    <p>{{ $area->description ?? 'No description available' }}</p>
                </div>
            </div>
            
            <h2>Parameters</h2>
            @if($area->parameters->count() > 0)
                @foreach($area->parameters as $parameter)
                <div class="card mb-3">
                    <div class="card-header" style="background-color: {{ $secondaryColor }}; color: white;">
                        {{ $parameter->name }}
                    </div>
                    <div class="card-body">
                        <p>{{ $parameter->description ?? 'No description available' }}</p>
                        
                        @if($parameter->indicators->count() > 0)
                            <h5>Indicators:</h5>
                            <ul class="list-group">
                                @foreach($parameter->indicators as $indicator)
                                <li class="list-group-item">{{ $indicator->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">No indicators for this parameter.</p>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                <p class="text-muted">No parameters found for this area.</p>
            @endif
        </div>
    </div>
</div>
@endsection