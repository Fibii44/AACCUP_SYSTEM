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
                    <!-- Status Messages -->
                    @if(session('success'))
                    <div class="alert alert-success mx-4">
                        {{ session('success') }}
                    </div>
                    @endif
                    
                    @if(session('error'))
                    <div class="alert alert-danger mx-4">
                        {{ session('error') }}
                    </div>
                    @endif
                    
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
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Image-based Color Palette Selection -->
                            <div class="col-md-12 mb-4">
                                <div class="form-group">
                                    <label class="form-control-label mb-3">Select a Color Palette</label>
                                    
                                    <div class="row">
                                   
                                        <!-- Professional Dark Palette -->
                                        <div class="col-md-4 mb-3">
                                            <div class="palette-option {{ ($settings->primary_color == '#6366F1' && $settings->secondary_color == '#8B5CF6' && $settings->tertiary_color == '#EC4899') ? 'selected' : '' }}" 
                                                 onclick="selectPalette('professional', '#6366F1', '#8B5CF6', '#EC4899', this)">
                                                <div class="palette-preview">
                                                    <div style="background-color: #6366F1; flex: 1;"></div>
                                                    <div style="background-color: #8B5CF6; flex: 1;"></div>
                                                    <div style="background-color: #EC4899; flex: 1;"></div>
                                                </div>
                                                <div class="palette-name">Modern Purple</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Red Combination Palette -->
                                        <div class="col-md-4 mb-3">
                                            <div class="palette-option {{ ($settings->primary_color == '#EF4444' && $settings->secondary_color == '#B91C1C' && $settings->tertiary_color == '#FCA5A5') ? 'selected' : '' }}" 
                                                 onclick="selectPalette('red', '#EF4444', '#B91C1C', '#FCA5A5', this)">
                                                <div class="palette-preview">
                                                    <div style="background-color: #EF4444; flex: 1;"></div>
                                                    <div style="background-color: #B91C1C; flex: 1;"></div>
                                                    <div style="background-color: #FCA5A5; flex: 1;"></div>
                                                </div>
                                                <div class="palette-name">Ruby Red</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Orange Combination Palette -->
                                        <div class="col-md-4 mb-3">
                                            <div class="palette-option {{ ($settings->primary_color == '#F97316' && $settings->secondary_color == '#FB923C' && $settings->tertiary_color == '#FFEDD5') ? 'selected' : '' }}" 
                                                 onclick="selectPalette('orange', '#F97316', '#FB923C', '#FFEDD5', this)">
                                                <div class="palette-preview">
                                                    <div style="background-color: #F97316; flex: 1;"></div>
                                                    <div style="background-color: #FB923C; flex: 1;"></div>
                                                    <div style="background-color: #FFEDD5; flex: 1;"></div>
                                                </div>
                                                <div class="palette-name">Amber Orange</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="palette-status" class="text-sm text-success mt-2" style="display: none;">
                                        Palette selected! Click Save Settings to apply changes.
                                    </div>
                                    
                                    <!-- Hidden color inputs for form submission -->
                                    <input type="hidden" id="primary_color" name="primary_color" value="{{ $settings->primary_color ?? '#3490dc' }}">
                                    <input type="hidden" id="secondary_color" name="secondary_color" value="{{ $settings->secondary_color ?? '#6c757d' }}">
                                    <input type="hidden" id="tertiary_color" name="tertiary_color" value="{{ $settings->tertiary_color ?? '#e3342f' }}">
                                    <input type="hidden" id="palette" name="palette" value="{{ 
                                        ($settings->primary_color == '#3490dc' && $settings->secondary_color == '#6c757d' && $settings->tertiary_color == '#e3342f') ? 'default' : 
                                        (($settings->primary_color == '#38c172' && $settings->secondary_color == '#4dc0b5' && $settings->tertiary_color == '#f6993f') ? 'nature' : 
                                        (($settings->primary_color == '#9561e2' && $settings->secondary_color == '#f66d9b' && $settings->tertiary_color == '#ffed4a') ? 'creative' : 
                                        (($settings->primary_color == '#6366F1' && $settings->secondary_color == '#8B5CF6' && $settings->tertiary_color == '#EC4899') ? 'professional' : 
                                        (($settings->primary_color == '#EF4444' && $settings->secondary_color == '#B91C1C' && $settings->tertiary_color == '#FCA5A5') ? 'red' : 
                                        (($settings->primary_color == '#F97316' && $settings->secondary_color == '#FB923C' && $settings->tertiary_color == '#FFEDD5') ? 'orange' : '')))))
                                    }}">
                                </div>
                            </div>

                            <!-- Header Text -->
                            <div class="col-md-12 mt-4">
                                <div class="form-group">
                                    <label for="header_text" class="form-control-label">Header Text</label>
                                    <input type="text" class="form-control" id="header_text" name="header_text" 
                                           placeholder="Enter your header text here..."
                                           value="{{ $settings->header_text ?? 'Welcome to Our Platform' }}">
                                    <small class="text-muted">This text will appear in the header of your pages</small>
                                </div>
                            </div>

                            <!-- Welcome Message -->
                            <div class="col-md-12 mt-4">
                                <div class="form-group">
                                    <label for="welcome_message" class="form-control-label">Welcome Message</label>
                                    <textarea class="form-control" id="welcome_message" name="welcome_message" rows="3" 
                                              placeholder="Enter your welcome message here...">{{ $settings->welcome_message ?? '' }}</textarea>
                                    <small class="text-muted">This message will be displayed on your landing page</small>
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

<style>
    .palette-option {
        cursor: pointer;
        border: 2px solid #eee;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    
    .palette-option:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .palette-option.selected {
        border-color: #5e72e4;
        box-shadow: 0 4px 10px rgba(94,114,228,0.3);
        transform: translateY(-3px);
    }
    
    .palette-preview {
        height: 60px;
        display: flex;
    }
    
    .palette-name {
        padding: 6px;
        text-align: center;
        background: #f8f9fa;
        font-weight: 600;
        font-size: 0.85rem;
    }
</style>

<script>
    function selectPalette(paletteId, primary, secondary, tertiary, element) {
        // Update hidden inputs
        document.getElementById('primary_color').value = primary;
        document.getElementById('secondary_color').value = secondary;
        document.getElementById('tertiary_color').value = tertiary;
        document.getElementById('palette').value = paletteId;
        
        // Update UI
        const allPalettes = document.querySelectorAll('.palette-option');
        allPalettes.forEach(palette => {
            palette.classList.remove('selected');
        });
        
        element.classList.add('selected');
        
        // Show success message
        document.getElementById('palette-status').style.display = 'block';
        
        // Optional: Log selection to console
        console.log(`Palette selected: ${paletteId}`, { primary, secondary, tertiary });
    }
</script>
@endsection