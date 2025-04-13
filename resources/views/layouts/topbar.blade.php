<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
  <div class="container-fluid py-1 px-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">
          @if(request()->is('dashboard*'))
            Dashboard
          @elseif(request()->is('user-table*'))
            User Table
          @elseif(request()->is('settings*'))
            Settings
          @else
            Dashboard
          @endif
        </li>
      </ol>
     
    </nav>
    <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
      <div class="ms-md-auto pe-md-3 d-flex align-items-center">
        <form action="{{ request()->is('user-table*') ? route('tenant.user-table') : '#' }}" method="GET" class="input-group input-group-custom">
          <span class="input-group-text text-body border-end-0 pe-2"><i class="fas fa-search" aria-hidden="true"></i></span>
          <input type="text" class="form-control ps-2" name="search" placeholder="Search..." style="border-left: none;" value="{{ request('search') }}">
          @if(request()->is('user-table*') && $statusFilter ?? false)
            <input type="hidden" name="status" value="{{ $statusFilter }}">
          @endif
        </form>
      </div>
      <ul class="navbar-nav justify-content-end align-items-center">
        <!-- Profile Dropdown -->
        <li class="nav-item dropdown pe-2 d-flex align-items-center">
          <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm position-relative">
                <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white" 
                     style="width: 32px; height: 32px; font-size: 0.875rem;">
                  {{ substr(auth()->user()->name, 0, 2) }}
                </div>
              </div>
              <div class="d-flex flex-column ms-2 me-2">
                <span class="text-sm font-weight-bold mb-0">{{ auth()->user()->name }}</span>
                <span class="text-xs text-secondary">{{ auth()->user()->email }}</span>
              </div>
              <i class="fas fa-chevron-down text-xs ms-1 text-secondary"></i>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton"
              style="min-width: 220px; box-shadow: 0 8px 26px -4px rgb(20 20 20 / 15%), 0 8px 9px -5px rgb(20 20 20 / 6%);">
            <li>
              <div class="dropdown-header border-bottom pb-2 mb-2">
                <p class="text-sm text-secondary mb-0">Welcome,</p>
                <h6 class="text-sm font-weight-bold mb-0">{{ auth()->user()->name }}</h6>
              </div>
            </li>
            <li class="mb-2">
              <a class="dropdown-item border-radius-md py-2 d-flex align-items-center" href="{{ route('settings.profile') }}">
                <i class="fas fa-cog text-primary me-2" style="font-size: 1rem;"></i>
                <span class="text-sm font-weight-normal">Settings</span>
              </a>
            </li>
            <li>
              <form method="POST" action="{{ route('tenant.logout') }}">
                @csrf
                <button type="submit" class="dropdown-item border-radius-md py-2 d-flex align-items-center">
                  <i class="fas fa-sign-out-alt text-danger me-2" style="font-size: 1rem;"></i>
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
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
            </div>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- End Navbar --> 