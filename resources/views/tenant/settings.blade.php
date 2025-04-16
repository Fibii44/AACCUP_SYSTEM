@extends('layouts.dashboardTemplate')

@section('content')
@php
    // Determine the current palette based on colors
    $currentPalette = 'default';
    
    if ($settings) {
        $primaryColor = $settings->primary_color;
        $secondaryColor = $settings->secondary_color;
        $tertiaryColor = $settings->tertiary_color;
        
        if ($primaryColor == '#3490dc' && $secondaryColor == '#6c757d' && $tertiaryColor == '#1a237e') {
            $currentPalette = 'dbdefault';
        } elseif ($primaryColor == '#3490dc' && $secondaryColor == '#6c757d' && $tertiaryColor == '#e3342f') {
            $currentPalette = 'default';
        } elseif ($primaryColor == '#38c172' && $secondaryColor == '#4dc0b5' && $tertiaryColor == '#f6993f') {
            $currentPalette = 'nature';
        } elseif ($primaryColor == '#9561e2' && $secondaryColor == '#f66d9b' && $tertiaryColor == '#ffed4a') {
            $currentPalette = 'creative';
        } elseif ($primaryColor == '#6366F1' && $secondaryColor == '#8B5CF6' && $tertiaryColor == '#EC4899') {
            $currentPalette = 'professional';
        } elseif ($primaryColor == '#EF4444' && $secondaryColor == '#B91C1C' && $tertiaryColor == '#FCA5A5') {
            $currentPalette = 'red';
        } elseif ($primaryColor == '#F97316' && $secondaryColor == '#FB923C' && $tertiaryColor == '#FFEDD5') {
            $currentPalette = 'orange';
        } elseif ($primaryColor == '#0EA5E9' && $secondaryColor == '#38BDF8' && $tertiaryColor == '#0C4A6E') {
            $currentPalette = 'blueocean';
        } elseif ($primaryColor == '#059669' && $secondaryColor == '#10B981' && $tertiaryColor == '#064E3B') {
            $currentPalette = 'emerald';
        }
    }
@endphp
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
                                        <h6 class="col-12 mb-3">Free Palettes</h6>
                                        <!-- DB Default Palette -->
                                        <div class="col-md-4 mb-3">
                                            <div class="palette-option {{ ($settings->primary_color == '#3490dc' && $settings->secondary_color == '#6c757d' && $settings->tertiary_color == '#000000') ? 'selected' : '' }}" 
                                                 onclick="selectPalette('dbdefault', '#3490dc', '#6c757d', '#1a237e', this)">
                                                <div class="palette-preview">
                                                    <div style="background-color: #3490dc; flex: 1;"></div>
                                                    <div style="background-color: #6c757d; flex: 1;"></div>
                                                    <div style="background-color: #000000; flex: 1;"></div>
                                                </div>
                                                <div class="palette-name">Database Default</div>
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

                                        <!-- Premium Palettes Section -->
                                        <h6 class="col-12 mt-4 mb-3">
                                            Premium Palettes 
                                            @if(!$isPremium)
                                                <span class="badge bg-warning text-dark ms-2">Premium Feature</span>
                                            @endif
                                        </h6>

                                        <!-- Professional Dark Palette (Premium) -->
                                        <div class="col-md-4 mb-3">
                                            <div class="palette-option {{ ($settings->primary_color == '#6366F1' && $settings->secondary_color == '#8B5CF6' && $settings->tertiary_color == '#EC4899') ? 'selected' : '' }} {{ !$isPremium ? 'premium-locked' : '' }}" 
                                                 onclick="{{ $isPremium ? "selectPalette('professional', '#6366F1', '#8B5CF6', '#EC4899', this)" : "showPremiumFeatureAlert()" }}">
                                                <div class="palette-preview {{ !$isPremium ? 'palette-blur' : '' }}">
                                                    <div style="background-color: #6366F1; flex: 1;"></div>
                                                    <div style="background-color: #8B5CF6; flex: 1;"></div>
                                                    <div style="background-color: #EC4899; flex: 1;"></div>
                                                </div>
                                                <div class="palette-name">Modern Purple 
                                                    @if(!$isPremium)
                                                        <i class="fas fa-lock float-end"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Blue Ocean Palette (Premium) -->
                                        <div class="col-md-4 mb-3">
                                            <div class="palette-option {{ ($settings->primary_color == '#0EA5E9' && $settings->secondary_color == '#38BDF8' && $settings->tertiary_color == '#0C4A6E') ? 'selected' : '' }} {{ !$isPremium ? 'premium-locked' : '' }}" 
                                                 onclick="{{ $isPremium ? "selectPalette('blueocean', '#0EA5E9', '#38BDF8', '#0C4A6E', this)" : "showPremiumFeatureAlert()" }}">
                                                <div class="palette-preview {{ !$isPremium ? 'palette-blur' : '' }}">
                                                    <div style="background-color: #0EA5E9; flex: 1;"></div>
                                                    <div style="background-color: #38BDF8; flex: 1;"></div>
                                                    <div style="background-color: #0C4A6E; flex: 1;"></div>
                                                </div>
                                                <div class="palette-name">Blue Ocean
                                                    @if(!$isPremium)
                                                        <i class="fas fa-lock float-end"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Emerald Green Palette (Premium) -->
                                        <div class="col-md-4 mb-3">
                                            <div class="palette-option {{ ($settings->primary_color == '#059669' && $settings->secondary_color == '#10B981' && $settings->tertiary_color == '#064E3B') ? 'selected' : '' }} {{ !$isPremium ? 'premium-locked' : '' }}" 
                                                 onclick="{{ $isPremium ? "selectPalette('emerald', '#059669', '#10B981', '#064E3B', this)" : "showPremiumFeatureAlert()" }}">
                                                <div class="palette-preview {{ !$isPremium ? 'palette-blur' : '' }}">
                                                    <div style="background-color: #059669; flex: 1;"></div>
                                                    <div style="background-color: #10B981; flex: 1;"></div>
                                                    <div style="background-color: #064E3B; flex: 1;"></div>
                                                </div>
                                                <div class="palette-name">Emerald Green
                                                    @if(!$isPremium)
                                                        <i class="fas fa-lock float-end"></i>
                                                    @endif
                                                </div>
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
                                    <input type="hidden" id="palette" name="palette" value="{{ $currentPalette }}">
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
                                <button type="submit" class="btn bg-gradient-primary" style="background-image: linear-gradient(310deg, {{ $settings->primary_color ?? '#3490dc' }} 0%, {{ $settings->secondary_color ?? '#6c757d' }} 100%);">Save Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Premium Feature Modal -->
<div class="modal fade" id="premiumFeatureModal" tabindex="-1" aria-labelledby="premiumFeatureModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="premiumFeatureModalLabel">Premium Feature</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>This color palette is available exclusively for Premium plan subscribers.</p>
        <p>Upgrade your plan to access premium color palettes and other exclusive features.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a href="{{ route('tenant.subscription') }}" class="btn bg-gradient-primary">View Subscription Plans</a>
      </div>
    </div>
  </div>
</div>

<style>
    @php
    $primaryColor = $settings->primary_color ?? '#3490dc';
    $secondaryColor = $settings->secondary_color ?? '#6c757d';
    $tertiaryColor = $settings->tertiary_color ?? '#1a237e';
    @endphp

    .card-header {
        color: {{ $tertiaryColor }};
    }
    
    .form-control-label {
        color: {{ $tertiaryColor }};
        font-weight: 600;
    }

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
        border-color: {{ $primaryColor }};
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
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
        color: {{ $tertiaryColor }};
    }

    #palette-status {
        color: {{ $primaryColor }} !important;
        font-weight: 600;
    }
    
    .premium-locked {
        position: relative;
        border: 2px solid #ffc107;
    }
    
    .palette-blur {
        filter: blur(2px);
    }
    
    .premium-locked .palette-name {
        background-color: #fef3c7;
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
    
    function showPremiumFeatureAlert() {
        // Show the bootstrap modal
        var premiumModal = new bootstrap.Modal(document.getElementById('premiumFeatureModal'));
        premiumModal.show();
    }
</script>
@endsection