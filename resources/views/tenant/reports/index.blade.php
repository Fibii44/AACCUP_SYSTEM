@extends('layouts.dashboardTemplate')

@section('content')
@push('head')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
@endpush

@php
    // Get the tenant settings
    $settings = \App\Models\TenantSetting::first() ?? new \App\Models\TenantSetting();
    
    // Get the colors with default fallbacks
    $primaryColor = $settings->primary_color ?? '#3490dc';
    $secondaryColor = $settings->secondary_color ?? '#6c757d';
    $tertiaryColor = $settings->tertiary_color ?? '#1a237e';
@endphp

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Instrument Submission Reports</h4>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-sm">Generate reports to track faculty submission status across instruments. Each report will include detailed information about who has submitted and who has not.</p>
                    
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    <form action="{{ route('tenant.reports.download') }}" method="POST" id="reportForm">
                        @csrf
                        <div class="mb-4">
                            <h5 class="mb-3">Select Instruments to Include</h5>
                            
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="checkAll(true)">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAll(false)">Deselect All</button>
                            </div>
                            
                            <div class="row">
                                @if($instruments->count() > 0)
                                    @foreach($instruments as $instrument)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="instruments[]" value="{{ $instrument->id }}" id="instrument-{{ $instrument->id }}">
                                            <label class="form-check-label d-flex justify-content-between" for="instrument-{{ $instrument->id }}">
                                                <span>{{ $instrument->name }}</span>
                                                <span class="badge bg-secondary ms-2">{{ $instrument->areas->count() }} areas</span>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            No instruments found. Create instruments first to generate reports.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" id="previewBtn" class="btn btn-secondary" style="background-color: {{ $secondaryColor }}; border-color: {{ $secondaryColor }};">
                                <i class="fas fa-eye me-2"></i>Preview
                            </button>
                            <button type="submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">
                                <i class="fas fa-file-pdf me-2"></i>Generate PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12" id="previewContainer" style="display: none;">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Report Preview</h5>
                </div>
                <div class="card-body p-0">
                    <div id="reportPreview" class="p-3">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading preview...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Function to check/uncheck all checkboxes
    function checkAll(check) {
        var checkboxes = document.querySelectorAll('input[name="instruments[]"]');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = check;
        }
    }

    $(document).ready(function() {
        // Handle preview
        $('#previewBtn').on('click', function() {
            const checkedInstruments = $('input[name="instruments[]"]:checked');
            
            if (checkedInstruments.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Instruments Selected',
                    text: 'Please select at least one instrument to preview the report.'
                });
                return;
            }
            
            // Show preview container
            $('#previewContainer').show();
            
            // Create URL with query parameters
            let params = new URLSearchParams();
            checkedInstruments.each(function() {
                params.append('instruments[]', $(this).val());
            });
            
            // Fetch preview with proper CSRF token
            $.ajax({
                url: '{{ route('tenant.reports.generate') }}?' + params.toString(),
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                success: function(html) {
                    $('#reportPreview').html(html);
                },
                error: function(error) {
                    $('#reportPreview').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error loading preview: ${error.statusText}
                        </div>
                    `);
                }
            });
        });
        
        // Validate form submission
        $('#reportForm').on('submit', function(e) {
            const checkedInstruments = $('input[name="instruments[]"]:checked');
            
            if (checkedInstruments.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'No Instruments Selected',
                    text: 'Please select at least one instrument to generate the report.'
                });
            }
        });
    });
</script>
@endpush 