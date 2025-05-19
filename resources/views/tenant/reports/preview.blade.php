@php
    // Get the tenant settings
    $settings = \App\Models\TenantSetting::first() ?? new \App\Models\TenantSetting();
    
    // Get the colors with default fallbacks
    $primaryColor = $settings->primary_color ?? '#3490dc';
    $secondaryColor = $settings->secondary_color ?? '#6c757d';
    $tertiaryColor = $settings->tertiary_color ?? '#1a237e';
    
    // Get tenant name
    $tenantName = $settings->site_name ?? tenant('id');
@endphp

<!-- Preview of the report content that will be in the PDF -->
<div class="report-preview">
    <div class="text-end mb-3">
        <span class="badge bg-secondary">PDF Preview</span>
    </div>
    
    @if($instruments->count() > 0)
        @foreach($instruments as $instrument)
            <div class="instrument-section mb-5">
                <div class="mb-3 border-bottom pb-2" style="border-color: {{ $primaryColor }} !important;">
                    <h4 class="text-center" style="color: {{ $primaryColor }};">{{ $instrument->name }}</h4>
                </div>
                
                @if($instrument->areas->count() > 0)
                    @foreach($instrument->areas as $area)
                        <div class="area-section mb-4">
                            <h5 class="mb-2" style="color: {{ $secondaryColor }};">{{ $area->name }}</h5>
                            
                            @if($area->parameters->count() > 0)
                                @foreach($area->parameters as $parameter)
                                    <div class="parameter-section mb-3">
                                        <h6 class="mb-2" style="color: {{ $tertiaryColor }};">{{ $parameter->name }}</h6>
                                        
                                        @if($parameter->indicators->count() > 0)
                                            @foreach($parameter->indicators as $indicator)
                                                <div class="indicator-section mb-2">
                                                    <p class="mb-1">{{ $indicator->name }}</p>
                                                    
                                                    @if($indicator->uploads->count() > 0)
                                                        <div class="uploads-section ms-3">
                                                            @foreach($indicator->uploads as $upload)
                                                                <div class="upload-item mb-1">
                                                                    <span class="text-muted">{{ $upload->file_name }}</span>
                                                                    <span class="text-muted ms-2">(Uploaded by: {{ $upload->user->name }})</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="text-muted ms-3">No files uploaded</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-muted ms-3">No indicators</div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted ms-3">No parameters</div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="text-muted">No areas</div>
                @endif
                
                <div class="mt-3 text-center text-muted">
                    <small><em>Page {{ $loop->iteration }} of {{ $instruments->count() }}</em></small>
                </div>
                
                @unless($loop->last)
                <hr class="my-4 dashed-divider">
                @endunless
            </div>
        @endforeach
        
        <div class="text-center mt-4 mb-2 p-2" style="border-top: 1px solid #dee2e6;">
            <small class="text-muted">{{ $tenantName }} | Generated: {{ now()->format('M d, Y h:i A') }}</small>
        </div>
        
    @else
        <div class="alert alert-info">
            No instruments selected for the report.
        </div>
    @endif
</div>

<style>
    .report-preview {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1) inset;
    }
    
    .dashed-divider {
        border-top: 2px dashed #dee2e6;
    }
    
    @media print {
        .report-preview {
            box-shadow: none;
            padding: 0;
        }
    }
</style> 