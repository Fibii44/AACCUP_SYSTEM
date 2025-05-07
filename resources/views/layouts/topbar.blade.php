@php
    // Get the tenant settings
    $settings = \App\Models\TenantSetting::first() ?? new \App\Models\TenantSetting();
    
    // Get the colors with default fallbacks
    $primaryColor = $settings->primary_color ?? '#3490dc';
    $secondaryColor = $settings->secondary_color ?? '#6c757d';
    $tertiaryColor = $settings->tertiary_color ?? '#1a237e';
    
    // Create a slightly lighter version of primary for hover effects
    $primaryColorLight = $primaryColor . '20'; // Adding 20% opacity
    
    // Get current app version
    $appVersion = config('self-update.version_installed') ?: 'v1.0.0';
    
    // Helper function to convert hex to RGB
    function hexToRgb($hex) {
        $hex = str_replace('#', '', $hex);
        
        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return "$r, $g, $b";
    }
@endphp
<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true" style="background-color: rgba(255, 255, 255, 0.95); box-shadow: 0 2px 12px 0 rgba({{ hexToRgb($primaryColor) }}, 0.15) !important;">
  <div class="container-fluid py-1 px-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm">
          <a class="opacity-5 text-dark" href="javascript:;">Pages</a>
        </li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">
          @if(request()->is('dashboard*'))
            Dashboard
          @elseif(request()->is('instruments') || request()->route()->getName() === 'tenant.instruments.index')
            Instruments
          @elseif(request()->is('instruments/*') && request()->route()->getName() === 'tenant.instruments.show')
            <a class="opacity-5 text-dark" href="{{ route('tenant.instruments.index') }}">Instruments</a> / {{ $instrument->name ?? 'Details' }}
          @elseif(request()->is('reports*') || request()->route()->getName() === 'tenant.reports.index')
            Reports
          @elseif(request()->is('user-table*'))
            User Table
          @elseif(request()->is('settings*'))
            Settings
          @elseif(request()->is('subscription*'))
            Subscription
          @elseif(request()->is('system-updates*'))
            System Updates
          @else
            Dashboard
          @endif
        </li>
      </ol>
    </nav>
    <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
      <div class="ms-md-auto pe-md-3 d-flex align-items-center">
        <form action="{{ request()->is('user-table*') ? route('tenant.user-table') : '#' }}" method="GET" class="input-group input-group-custom">
          <span class="input-group-text text-body border-end-0 pe-2"><i class="fas fa-search" aria-hidden="true" style="color: {{ $primaryColor }};"></i></span>
          <input type="text" class="form-control ps-2" name="search" placeholder="Search..." style="border-left: none; border-color: #eee;" value="{{ request('search') }}">
          @if(request()->is('user-table*') && $statusFilter ?? false)
            <input type="hidden" name="status" value="{{ $statusFilter }}">
          @endif
        </form>
      </div>
      <ul class="navbar-nav justify-content-end align-items-center">
        <!-- Subscription Button - Shows "Manage Plan" for premium and "Upgrade" for free -->
        <li class="nav-item pe-3">
          @php
              try {
                  // Get the current tenant from tenant() helper
                  $tenant = tenant();
                  $tenantId = $tenant->id;
                  
                  // Use the mysql connection to query the tenants table in the central database
                  $tenantPlan = DB::connection('mysql')->table('tenants')->where('id', $tenantId)->value('plan');
                  
                  // Set flag based on plan value
                  $isPremiumPlan = ($tenantPlan === 'premium');
              } catch (\Exception $e) {
                  // Default to free plan if there's an error
                  $isPremiumPlan = false;
                  
                  // Log the error
                  \Log::error('Error determining tenant plan: ' . $e->getMessage());
              }
          @endphp
          
          <a href="{{ route('tenant.subscription') }}" class="btn btn-sm {{ $isPremiumPlan ? 'btn-light border' : 'btn-primary' }} mb-0" style="{{ $isPremiumPlan ? 'color: ' . $primaryColor . ';' : 'background-color: ' . $primaryColor . '; border-color: ' . $primaryColor . ';' }}">
            @if($isPremiumPlan)
              <i class="fas fa-cog me-1"></i> Manage Plan
            @else
              <i class="fas fa-crown me-1"></i> Upgrade
            @endif
          </a>
        </li>
        
        <!-- Version Button -->
        <li class="nav-item pe-3">
          <a href="{{ route('tenant.system-updates.index') }}" class="btn btn-sm btn-outline-secondary mb-0">
            <i class="fas fa-code-branch me-1"></i> {{ $appVersion }}
          </a>
        </li>
        
        <!-- Profile Dropdown -->
        <li class="nav-item dropdown pe-2 d-flex align-items-center">
          <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm position-relative">
                @if(auth()->user()->profile_picture)
                  <img src="{{ auth()->user()->profile_picture }}" alt="{{ auth()->user()->name }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                @else
                  <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white" 
                       style="width: 32px; height: 32px; font-size: 0.875rem; background: {{ $primaryColor }};">
                    {{ substr(auth()->user()->name, 0, 2) }}
                  </div>
                @endif
              </div>
              <div class="d-flex flex-column ms-2 me-2">
                <span class="text-sm font-weight-bold mb-0" style="color: {{ $tertiaryColor }};">{{ auth()->user()->name }}</span>
                <span class="text-xs text-secondary" style="color: {{ $secondaryColor }} !important;">{{ auth()->user()->email }}</span>
              </div>
              <i class="fas fa-chevron-down text-xs ms-1 text-secondary" style="color: {{ $primaryColor }} !important;"></i>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton"
              style="min-width: 220px; box-shadow: 0 8px 26px -4px rgba({{ hexToRgb($primaryColor) }}, 0.15), 0 8px 9px -5px rgba({{ hexToRgb($primaryColor) }}, 0.06); border-top: 3px solid {{ $primaryColor }};">
            <li>
              <div class="dropdown-header border-bottom pb-2 mb-2">
                <div class="d-flex align-items-center mb-2">
                  @if(auth()->user()->profile_picture)
                    <img src="{{ auth()->user()->profile_picture }}" alt="{{ auth()->user()->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                  @else
                    <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2" 
                         style="width: 40px; height: 40px; font-size: 1rem; background: {{ $primaryColor }};">
                      {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                  @endif
                  <div>
                    <p class="text-sm text-secondary mb-0">Welcome,</p>
                    <h6 class="text-sm font-weight-bold mb-0" style="color: {{ $tertiaryColor }};">{{ auth()->user()->name }}</h6>
                  </div>
                </div>
              </div>
            </li>
            <li class="mb-2">
              <a class="dropdown-item border-radius-md py-2 d-flex align-items-center" href="{{ route('settings.profile') }}">
                <i class="fas fa-cog text-primary me-2" style="font-size: 1rem; color: {{ $primaryColor }} !important;"></i>
                <span class="text-sm font-weight-normal">Settings</span>
              </a>
            </li>
            <!-- Add System Updates link in dropdown menu -->
            <li class="mb-2">
              <a class="dropdown-item border-radius-md py-2 d-flex align-items-center" href="{{ route('tenant.system-updates.index') }}">
                <i class="fas fa-code-branch text-primary me-2" style="font-size: 1rem; color: {{ $primaryColor }} !important;"></i>
                <span class="text-sm font-weight-normal">System Updates</span>
              </a>
            </li>
            <li>
              <form method="POST" action="{{ route('tenant.logout') }}">
                @csrf
                <button type="submit" class="dropdown-item border-radius-md py-2 d-flex align-items-center">
                  <i class="fas fa-sign-out-alt text-danger me-2" style="font-size: 1rem; color: {{ $primaryColor }} !important;"></i>
                  <span class="text-sm font-weight-normal">Logout</span>
                </button>
              </form>
            </li>
          </ul>
        </li>
        <!-- Mobile Menu Toggle -->
        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
          <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line" style="background-color: {{ $primaryColor }};"></i>
              <i class="sidenav-toggler-line" style="background-color: {{ $primaryColor }};"></i>
              <i class="sidenav-toggler-line" style="background-color: {{ $primaryColor }};"></i>
            </div>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- End Navbar --> 