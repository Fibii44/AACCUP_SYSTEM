@extends('layouts.dashboardTemplate')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Tenant Settings</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <form action="{{ route('tenant.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        
                        <div class="row p-4">
                            <!-- Logo Upload -->
                            <div class="col-md-12 mb-4">
                                <div class="form-group">
                                    <label for="logo_url" class="form-control-label">Logo URL</label>
                                    <input type="url" class="form-control" id="logo_url" name="logo_url" 
                                           placeholder="https://example.com/logo.png" 
                                           value="{{ $settings->logo_url ?? '' }}">
                                    @if($settings && $settings->logo_url)
                                        <div class="mt-2">
                                            <p>Current Logo:</p>
                                            <img src="{{ $settings->logo_url }}" alt="Current Logo" style="max-height: 100px;">
                                            <p class="text-sm text-muted mt-1">Current URL: {{ $settings->logo_url }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Primary Color -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primary_color" class="form-control-label">Primary Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="primary_color" 
                                               name="primary_color" value="{{ $settings->primary_color ?? '#3490dc' }}">
                                        <input type="text" class="form-control" value="{{ $settings->primary_color ?? '#3490dc' }}" 
                                               id="primary_color_text" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Secondary Color -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="secondary_color" class="form-control-label">Secondary Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="secondary_color" 
                                               name="secondary_color" value="{{ $settings->secondary_color ?? '#6c757d' }}">
                                        <input type="text" class="form-control" value="{{ $settings->secondary_color ?? '#6c757d' }}" 
                                               id="secondary_color_text" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Text -->
                            <div class="col-md-12 mt-4">
                                <div class="form-group">
                                    <label for="footer_text" class="form-control-label">Footer Text</label>
                                    <textarea class="form-control" id="footer_text" name="footer_text" rows="3" 
                                              placeholder="Enter your footer text here...">{{ $settings->footer_text ?? '' }}</textarea>
                                    <small class="text-muted">This text will appear at the bottom of your pages</small>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-md-12 mt-4">
                                <button type="submit" class="btn bg-gradient-primary">Save Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Update color text inputs when color picker changes
    document.getElementById('primary_color').addEventListener('input', function() {
        document.getElementById('primary_color_text').value = this.value;
    });

    document.getElementById('secondary_color').addEventListener('input', function() {
        document.getElementById('secondary_color_text').value = this.value;
    });

    // Update color pickers when text inputs change
    document.getElementById('primary_color_text').addEventListener('input', function() {
        document.getElementById('primary_color').value = this.value;
    });

    document.getElementById('secondary_color_text').addEventListener('input', function() {
        document.getElementById('secondary_color').value = this.value;
    });
</script>
@endpush
@endsection