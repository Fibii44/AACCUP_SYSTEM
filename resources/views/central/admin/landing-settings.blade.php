@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Landing Page Settings</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.landing-settings.update') }}">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="primary_color" class="form-label">Primary Color</label>
                                    <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="{{ $settings->primary_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="secondary_color" class="form-label">Secondary Color</label>
                                    <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" value="{{ $settings->secondary_color }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="logo_url" class="form-label">Logo URL</label>
                            <input type="text" class="form-control" id="logo_url" name="logo_url" value="{{ $settings->logo_url }}">
                            <small class="text-muted">Enter a full URL to your logo image</small>
                        </div>

                        <div class="mb-3">
                            <label for="header_text" class="form-label">Header Text</label>
                            <input type="text" class="form-control" id="header_text" name="header_text" value="{{ $settings->header_text }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="welcome_message" class="form-label">Welcome Message</label>
                            <textarea class="form-control" id="welcome_message" name="welcome_message" rows="3">{{ $settings->welcome_message }}</textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="show_testimonials" name="show_testimonials" value="1" {{ $settings->show_testimonials ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_testimonials">Show Testimonials Section</label>
                        </div>

                        <div class="mb-3">
                            <label for="footer_text" class="form-label">Footer Text</label>
                            <input type="text" class="form-control" id="footer_text" name="footer_text" value="{{ $settings->footer_text }}">
                        </div>

                        <div class="mb-3">
                            <label for="custom_css" class="form-label">Custom CSS (Advanced)</label>
                            <textarea class="form-control font-monospace" id="custom_css" name="custom_css" rows="5">{{ $settings->custom_css ? json_encode($settings->custom_css, JSON_PRETTY_PRINT) : '' }}</textarea>
                            <small class="text-muted">Enter custom CSS properties in JSON format</small>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                            <a href="{{ route('landing') }}" class="btn btn-outline-secondary ms-2" target="_blank">Preview Landing Page</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 