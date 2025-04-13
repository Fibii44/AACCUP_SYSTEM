@extends('layouts.dashboardTemplate')

@section('content')
@php
    // Get the tenant settings
    $settings = \App\Models\TenantSetting::first() ?? new \App\Models\TenantSetting();
    
    // Get the colors with default fallbacks
    $primaryColor = $settings->primary_color ?? '#3490dc';
    $secondaryColor = $settings->secondary_color ?? '#6c757d';
    $tertiaryColor = $settings->tertiary_color ?? '#1a237e';
@endphp

<style>
    .card-header h6 {
        color: {{ $tertiaryColor }};
        font-weight: 600;
    }
    
    .bg-gradient-primary {
        background-image: linear-gradient(310deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
    }
    
    .bg-gradient-warning {
        background-image: linear-gradient(310deg, #fbb140 0%, {{ $secondaryColor }} 100%);
    }
    
    .bg-gradient-success {
        background-image: linear-gradient(310deg, #2dce89 0%, {{ $primaryColor }} 100%);
    }
    
    .bg-gradient-info {
        background-image: linear-gradient(310deg, {{ $primaryColor }} 0%, #1171ef 100%);
    }
    
    .btn-primary {
        background-color: {{ $primaryColor }};
        border-color: {{ $primaryColor }};
    }
    
    .btn-primary:hover {
        background-color: {{ $primaryColor }}dd;
        border-color: {{ $primaryColor }};
    }
    
    .input-group-text {
        border-color: {{ $secondaryColor }}40;
    }
    
    .form-control:focus {
        border-color: {{ $primaryColor }};
    }
</style>

<div class="row">
    <div class="col-12">
        <!-- Status Filter and Search -->
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div class="d-flex">
                <form action="{{ route('tenant.user-table') }}" method="GET" class="d-flex align-items-center">
                    <div class="input-group me-3">
                        <span class="input-group-text" style="height: 45px;"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control form-control-sm" name="search" placeholder="Search by name or email" style="height: 45px;" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center" style="height: 45px;">Search</button>
                        @if(request('search'))
                            <a href="{{ route('tenant.user-table', ['status' => $statusFilter]) }}" class="btn btn-sm btn-outline-secondary ms-1 d-flex align-items-center" style="height: 45px;">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        @endif
                    </div>
                    <!-- Preserve status parameter when searching -->
                    @if($statusFilter !== 'all')
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                    @endif
                </form>
            </div>
            
            <form action="{{ route('tenant.user-table') }}" method="GET" class="d-flex align-items-center">
                <label for="statusFilter" class="me-2 mb-0" style="color: {{ $tertiaryColor }}; font-weight: 500;">Filter by status:</label>
                <select name="status" id="statusFilter" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Users</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Users</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Archived Users</option>
                </select>
                <!-- Preserve search parameter when changing status filter -->
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
            </form>
        </div>
        
        <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6>Users</h6>
                 <!-- Add Faculty Button -->
                 <button type="button" class="btn bg-gradient-primary" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
                    <i class="bi bi-person-plus me-2"></i>
                    <span class="btn-inner--text">Add Faculty</span>
                </button>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="color: {{ $tertiaryColor }}!important;">Name</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="color: {{ $tertiaryColor }}!important;">Role</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="color: {{ $tertiaryColor }}!important;">Status</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="color: {{ $tertiaryColor }}!important;">Date Added</th>        
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="color: {{ $tertiaryColor }}!important;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($faculty->count() > 0)
                                @foreach($faculty as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $member->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $member->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-success">User</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm {{ $member->status === 'active' ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                            {{ ucfirst($member->status) }}
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <span class="text-sm font-weight-normal">
                                            {{ $member->created_at->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-sm bg-gradient-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editFacultyModal" 
                                                    data-faculty-id="{{ $member->id }}"
                                                    data-faculty-name="{{ $member->name }}"
                                                    data-faculty-email="{{ $member->email }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>

                                            <!-- Archive Button -->
                                            <button type="button" class="btn btn-sm {{ $member->status === 'active' ? 'bg-gradient-warning' : 'bg-gradient-success' }}" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#archiveFacultyModal"
                                                    data-faculty-id="{{ $member->id }}"
                                                    data-faculty-name="{{ $member->name }}"
                                                    data-faculty-status="{{ $member->status }}">
                                                <i class="fas fa-{{ $member->status === 'active' ? 'archive' : 'check-circle' }}"></i> {{ $member->status === 'active' ? 'Archive' : 'Restore' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-users-slash text-secondary mb-2" style="font-size: 2rem;"></i>
                                            <p class="mb-0">
                                                @if(request('search'))
                                                    No users found matching "{{ request('search') }}" {{ $statusFilter !== 'all' ? 'with ' . $statusFilter . ' status' : '' }}.
                                                @else
                                                    No users found matching the selected filter.
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addFacultyModal" tabindex="-1" role="dialog" aria-labelledby="addFacultyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFacultyModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('tenant.faculty.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="name" class="form-control-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter user name" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-control-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter user email" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-gradient-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Edit User Modal -->
<div class="modal fade" id="editFacultyModal" tabindex="-1" role="dialog" aria-labelledby="editFacultyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFacultyModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editFacultyForm" action="" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="edit_name" class="form-control-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="form-control-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-gradient-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Archive User Modal -->
<div class="modal fade" id="archiveFacultyModal" tabindex="-1" role="dialog" aria-labelledby="archiveFacultyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveFacultyModalLabel">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="archiveFacultyForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>Are you sure you want to <span id="archiveActionText" class="font-weight-bold"></span> this user: <span id="archiveUserName" class="font-weight-bold"></span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn bg-gradient-warning" id="archiveSubmitBtn">Archive User</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Toast Messages -->
@if(session('success') || session('error') || $errors->any())
<div class="position-fixed bottom-1 end-1 z-index-2">
    <div class="toast fade show p-2 bg-white" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header border-0">
            @if(session('success'))
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="me-auto font-weight-bold">Success</span>
            @elseif(session('error'))
                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                <span class="me-auto font-weight-bold">Error</span>
            @else
                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                <span class="me-auto font-weight-bold">Validation Failed</span>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <hr class="horizontal dark m-0">
        <div class="toast-body">
            @if(session('success'))
                {{ session('success') }}
            @elseif(session('error'))
                {{ session('error') }}
            @else
                <ul class="list-unstyled mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Existing modal reset code
    const addFacultyModal = document.getElementById('addFacultyModal');
    addFacultyModal.addEventListener('hidden.bs.modal', function () {
        const form = this.querySelector('form');
        form.reset();
    });

    // Edit modal functionality
    const editFacultyModal = document.getElementById('editFacultyModal');
    const editForm = document.getElementById('editFacultyForm');
    
    document.querySelectorAll('[data-bs-target="#editFacultyModal"]').forEach(button => {
        button.addEventListener('click', async function() {
            const facultyId = this.getAttribute('data-faculty-id');
            const facultyName = this.getAttribute('data-faculty-name');
            const facultyEmail = this.getAttribute('data-faculty-email');
            
            // Update the form action URL to match the route
            editForm.action = `/faculty/${facultyId}`; // Changed this line
            
            // Set form values
            document.getElementById('edit_name').value = facultyName;
            document.getElementById('edit_email').value = facultyEmail;
        });
    });

    // Archive modal functionality
    const archiveFacultyModal = document.getElementById('archiveFacultyModal');
    const archiveForm = document.getElementById('archiveFacultyForm');
    const archiveUserNameSpan = document.getElementById('archiveUserName');
    const archiveActionTextSpan = document.getElementById('archiveActionText');
    const archiveSubmitBtn = document.getElementById('archiveSubmitBtn');

    document.querySelectorAll('[data-bs-target="#archiveFacultyModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const facultyId = this.getAttribute('data-faculty-id');
            const facultyName = this.getAttribute('data-faculty-name');
            const facultyStatus = this.getAttribute('data-faculty-status');
            
            // Update the form action URL
            archiveForm.action = `/faculty/${facultyId}/status`;
            
            // Update the user name in the confirmation message
            archiveUserNameSpan.textContent = facultyName;
            
            // Update the action text and button text based on current status
            if (facultyStatus === 'active') {
                archiveActionTextSpan.textContent = 'archive';
                archiveSubmitBtn.textContent = 'Archive User';
                archiveSubmitBtn.classList.remove('bg-gradient-success');
                archiveSubmitBtn.classList.add('bg-gradient-warning');
            } else {
                archiveActionTextSpan.textContent = 'restore';
                archiveSubmitBtn.textContent = 'Restore User';
                archiveSubmitBtn.classList.remove('bg-gradient-warning');
                archiveSubmitBtn.classList.add('bg-gradient-success');
            }
        });
    });

    // Toast auto-hide functionality
    const toast = document.querySelector('.toast');
    if (toast) {
        // For validation errors with multiple messages, give more time to read
        const hasValidationErrors = toast.querySelectorAll('.toast-body ul li').length > 0;
        const timeout = hasValidationErrors ? 7000 : 5000; // 7 seconds for validation errors, 5 for others

        setTimeout(function() {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.hide();
        }, timeout);
    }
});
</script>
@endsection