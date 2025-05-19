@extends('layouts.nestedTemplate')

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

<!-- Load Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="container-fluid">
    <!-- Display success messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Display error messages -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $area->name }}</h4>
                        <div>
                            @if(Auth::user()->role == 'admin')
                                <button type="button" class="btn btn-primary btn-sm me-2" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};" data-bs-toggle="modal" data-bs-target="#addParameterModal">
                                    <i class="fas fa-plus"></i> Create Parameter
                                </button>
                            @endif
                            <a href="{{ route('tenant.instruments.show', $instrument->id) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Drive Folder:</strong> 
                                @if($area->google_drive_folder_id)
                                    <i class="fas fa-check-circle text-success"></i> Created
                                @else
                                    <i class="fas fa-times-circle text-danger"></i> Not Created
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Parameters Count:</strong> <span id="parameters-count">{{ $area->parameters->count() }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>
            
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Parameters</h5>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div id="parameters-container" class="p-3">
                        @if($area->parameters->count() > 0)
                            @foreach($area->parameters as $parameter)
                            <div class="card mb-3 parameter-card" data-id="{{ $parameter->id }}">
                                <div class="card-body py-3">
                                    <div class="parameter-header d-flex justify-content-between align-items-center" 
                                         onclick="toggleParameterContent('parameter-content-{{ $parameter->id }}')" style="cursor: pointer">
                                        <div class="d-flex align-items-center">
                                            <h5 class="mb-0">{{ $parameter->name }}</h5>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-3">{{ $parameter->indicators->count() }} Indicators</span>
                                            @if(auth()->user()->role === 'admin')
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-sm px-2 py-1 rounded" id="dropdownMenuButton-{{ $parameter->id }}" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation()">
                                                    <i class="fas fa-ellipsis-h fa-lg"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton-{{ $parameter->id }}">
                                                    <li>
                                                        <a class="dropdown-item edit-parameter" href="#" data-id="{{ $parameter->id }}" data-name="{{ $parameter->name }}" onclick="editParameter({{ $parameter->id }}, '{{ $parameter->name }}'); return false;">
                                                            <i class="fas fa-edit me-2"></i>Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger delete-parameter" href="#" data-id="{{ $parameter->id }}" onclick="deleteParameter({{ $parameter->id }}); return false;">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div id="parameter-content-{{ $parameter->id }}" class="parameter-content mt-3" style="display: none;">
                                        <div class="border-top pt-3">
                        @if($parameter->indicators->count() > 0)
                                                <div class="list-group indicator-list mb-3">
                                @foreach($parameter->indicators as $indicator)
                                                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-clipboard-list me-2 text-secondary"></i>
                                                            <span class="indicator-name">{{ $indicator->name }}</span>
                                                            @if($indicator->uploads && $indicator->uploads->count() > 0)
                                                            <span class="badge bg-success ms-2">{{ $indicator->uploads->count() }}</span>
                                                            @endif
                                                            <small class="text-muted ms-3">
                                                                @if($indicator->created_at)
                                                                Added {{ $indicator->created_at->format('M d') }}
                                                                @endif
                                                            </small>
                                                            @if($indicator->google_drive_folder_id)
                                                            <span class="ms-2" title="Google Drive Folder Created"><i class="fas fa-check-circle text-success"></i></span>
                                                            @else
                                                            <span class="ms-2" title="No Google Drive Folder"><i class="fas fa-times-circle text-danger"></i></span>
                                                            @endif
                                                        </div>
                                                        
                                                        <div class="d-flex align-items-center">
                                                            @if(auth()->user()->role === 'user')
                                                            <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="uploadFile({{ $indicator->id }}); return false;">
                                                                <i class="fas fa-upload"></i> Upload
                                                            </button>
                                                            @endif

                                                            @if($indicator->google_drive_folder_id)
                                                            <a href="https://drive.google.com/drive/folders/{{ $indicator->google_drive_folder_id }}" target="_blank" class="btn btn-sm btn-outline-info me-2" title="View Files in Google Drive">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            @endif

                                                            @if($indicator->uploads && $indicator->uploads->count() > 0)
                                                            <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="viewUploads({{ $indicator->id }}); return false;">
                                                                <i class="fas fa-folder-open"></i> Files ({{ $indicator->uploads->count() }})
                                                            </button>
                                                            @endif

                                                            @if(auth()->user()->role === 'admin')
                                                            <div class="dropdown d-inline-block">
                                                                <button class="btn btn-sm btn-outline-secondary px-2 py-1" type="button" id="actionButton-{{ $indicator->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionButton-{{ $indicator->id }}">
                                                                    <li>
                                                                        <a class="dropdown-item" href="#" onclick="editIndicator({{ $indicator->id }}, '{{ $indicator->name }}'); return false;">
                                                                            <i class="fas fa-edit me-2"></i>Edit
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item text-danger" href="#" onclick="deleteIndicator({{ $indicator->id }}); return false;">
                                                                            <i class="fas fa-trash me-2"></i>Delete
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-3">
                                                    <p class="text-muted mb-0">No indicators added yet</p>
                                                </div>
                                            @endif
                                            
                                            @if(auth()->user()->role === 'admin')
                                            <div class="text-center">
                                                <button type="button" class="btn btn-outline-primary w-100" onclick="addIndicator({{ $parameter->id }})" style="border-style: dashed;">
                                                    <i class="fas fa-plus me-2"></i>Add Indicator
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                                @endforeach
                        @else
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">No parameters have been created for this area yet.</p>
                                @if(Auth::user()->role == 'admin')
                                    <button type="button" class="btn btn-primary mt-3" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};" data-bs-toggle="modal" data-bs-target="#addParameterModal">
                                        <i class="fas fa-plus me-1"></i> Create Your First Parameter
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Parameter Modal -->
<div class="modal fade" id="addParameterModal" tabindex="-1" aria-labelledby="addParameterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addParameterModalLabel">Create New Parameter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createParameterForm" action="{{ route('tenant.parameters.store', ['area' => $area->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="area_id" value="{{ $area->id }}">
                    <div class="mb-3">
                        <label for="parameter_name" class="form-label">Parameter Name</label>
                        <input type="text" class="form-control" id="parameter_name" name="name" required>
                    </div>
                    <button type="submit" id="create-parameter-submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Parameter Modal -->
<div class="modal fade" id="editParameterModal" tabindex="-1" aria-labelledby="editParameterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editParameterModalLabel">Edit Parameter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editParameterForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="edit_parameter_name" class="form-label">Parameter Name</label>
                        <input type="text" class="form-control" id="edit_parameter_name" name="name" required>
                    </div>
                    <button type="submit" id="edit-parameter-submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Indicator Modal -->
<div class="modal fade" id="addIndicatorModal" tabindex="-1" aria-labelledby="addIndicatorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIndicatorModalLabel">Add New Indicator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createIndicatorForm" method="POST">
                    @csrf
                    <input type="hidden" name="parameter_id" id="indicator_parameter_id">
                    <div class="mb-3">
                        <label for="indicator_name" class="form-label">Indicator Name</label>
                        <input type="text" class="form-control" id="indicator_name" name="name" required>
                    </div>
                    <button type="submit" id="create-indicator-submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Indicator Modal -->
<div class="modal fade" id="editIndicatorModal" tabindex="-1" aria-labelledby="editIndicatorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIndicatorModalLabel">Edit Indicator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editIndicatorForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="edit_indicator_name" class="form-label">Indicator Name</label>
                        <input type="text" class="form-control" id="edit_indicator_name" name="name" required>
                    </div>
                    <button type="submit" id="edit-indicator-submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Upload File Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadFileModalLabel">Upload Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadFileForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="indicator_id" id="upload_indicator_id">
                    <div class="mb-3">
                        <label for="file_upload" class="form-label">Select Files</label>
                        <input type="file" class="form-control" id="file_upload" name="files[]" multiple required>
                        <div class="form-text">
                            You can select multiple files to upload at once. Maximum file size: 40MB per file.
                        </div>
                    </div>
                    <button type="submit" id="upload-file-submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Uploads Modal -->
<div class="modal fade" id="viewUploadsModal" tabindex="-1" aria-labelledby="viewUploadsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUploadsModalLabel">Uploaded Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="uploads-container" class="list-group">
                    <!-- Uploaded files will be displayed here -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection 

@push('styles')
<style>
    .parameter-card {
        transition: all 0.2s ease;
        border: 1px solid #eee;
    }
    .parameter-card:hover {
        border-color: #aaa;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .parameter-header {
        position: relative;
    }
    .parameter-header:hover {
        background-color: #f8f9fa;
    }
    .parameter-content {
        transition: max-height 0.5s ease-in-out;
    }
    .btn-outline-secondary {
        border: 1px solid #ced4da;
    }
    .dropdown-toggle::after {
        display: none;
    }
    .fa-ellipsis-h, .fa-ellipsis-v {
        font-size: 1.1rem;
        color: #6c757d;
    }
    .dropdown-menu-end {
        right: 0;
        left: auto;
    }
    .indicator-list .list-group-item {
        border-left: none;
        border-right: none;
        padding: 0.75rem 0.5rem;
    }
    .indicator-list .list-group-item:first-child {
        border-top: none;
    }
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .indicator-name {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
</style>
@endpush

<script>
    // Wait for the document to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Document ready');
        
        // Set up form submission handler for create parameter
        const createParameterForm = document.getElementById('createParameterForm');
        if (createParameterForm) {
            createParameterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = document.getElementById('create-parameter-submit');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...';
                submitBtn.disabled = true;
                
                const formData = new FormData(this);
                const url = this.getAttribute('action');
                
                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    return response.json().then(data => {
                        if (!response.ok) {
                            throw { status: response.status, data: data };
                        }
                        return data;
                    });
                })
                .then(data => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Close the modal
                    bootstrap.Modal.getInstance(document.getElementById('addParameterModal')).hide();
                    
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Parameter created successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page
                        window.location.reload();
                    });
                })
                .catch(error => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    console.error('Error:', error);
                    
                    let errorMessage = 'Name already exists';
                    
                    // Handle validation errors
                    if (error.status === 422 && error.data) {
                        if (error.data.message) {
                            errorMessage = error.data.message;
                        } else if (error.data.errors && error.data.errors.name) {
                            errorMessage = error.data.errors.name[0];
                        }
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        }
        
        // Set up form submission handler for create indicator
        const createIndicatorForm = document.getElementById('createIndicatorForm');
        if (createIndicatorForm) {
            createIndicatorForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = document.getElementById('create-indicator-submit');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...';
                submitBtn.disabled = true;
                
                const formData = new FormData(this);
                const url = this.getAttribute('action');
                
                console.log('Submitting to URL:', url);
                
                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error('Server responded with: ' + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Close the modal
                    bootstrap.Modal.getInstance(document.getElementById('addIndicatorModal')).hide();
                    
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Indicator created successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page
                        window.location.reload();
                    });
                })
                .catch(error => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to create indicator: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        }
        
        // Set up form submission handler for edit parameter
        const editParameterForm = document.getElementById('editParameterForm');
        if (editParameterForm) {
            editParameterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = document.getElementById('edit-parameter-submit');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
                submitBtn.disabled = true;
                
                const form = this;
                const url = form.getAttribute('action');
                const nameField = document.getElementById('edit_parameter_name');
                const name = nameField.value;
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        name: name
                    })
                })
                .then(response => {
                    return response.json().then(data => {
                        if (!response.ok) {
                            throw { status: response.status, data: data };
                        }
                        return data;
                    });
                })
                .then(data => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    bootstrap.Modal.getInstance(document.getElementById('editParameterModal')).hide();
                    Swal.fire({
                        title: 'Success!',
                        text: 'Parameter updated successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    console.error('Error:', error);
                    
                    let errorMessage = 'Name already exists';
                    
                    // Handle validation errors
                    if (error.status === 422 && error.data) {
                        if (error.data.message) {
                            errorMessage = error.data.message;
                        } else if (error.data.errors && error.data.errors.name) {
                            errorMessage = error.data.errors.name[0];
                        }
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        }
        
        // Set up form submission handler for edit indicator
        const editIndicatorForm = document.getElementById('editIndicatorForm');
        if (editIndicatorForm) {
            editIndicatorForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = document.getElementById('edit-indicator-submit');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
                submitBtn.disabled = true;
                
                const form = this;
                const url = form.getAttribute('action');
                const nameField = document.getElementById('edit_indicator_name');
                const name = nameField.value;
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                console.log('Submitting update to URL:', url);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        name: name
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error('Server responded with: ' + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    bootstrap.Modal.getInstance(document.getElementById('editIndicatorModal')).hide();
                    Swal.fire({
                        title: 'Success!',
                        text: 'Indicator updated successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update indicator: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        }
        
        // Set up form submission handler for file upload
        const uploadFileForm = document.getElementById('uploadFileForm');
        if (uploadFileForm) {
            uploadFileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate file sizes before submitting
                const fileInput = document.getElementById('file_upload');
                const files = fileInput.files;
                const maxSize = 40 * 1024 * 1024; // 40MB in bytes
                let oversizedFiles = [];
                
                for (let i = 0; i < files.length; i++) {
                    if (files[i].size > maxSize) {
                        oversizedFiles.push(files[i].name + ' (' + Math.round(files[i].size / (1024 * 1024)) + 'MB)');
                    }
                }
                
                if (oversizedFiles.length > 0) {
                    Swal.fire({
                        title: 'Files Too Large',
                        html: 'The following files exceed the 40MB limit:<br>' + oversizedFiles.join('<br>'),
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
                
                // Show loading state
                const submitBtn = document.getElementById('upload-file-submit');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...';
                submitBtn.disabled = true;
                
                const formData = new FormData(this);
                const url = this.getAttribute('action');
                
                console.log('Submitting file upload to URL:', url);
                
                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error('Server responded with: ' + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Close the modal
                    bootstrap.Modal.getInstance(document.getElementById('uploadFileModal')).hide();
                    
                    // Show success message with any warnings
                    let title = 'Success!';
                    let text = 'Files uploaded successfully';
                    let icon = 'success';
                    
                    if (data.gdrive_warnings && data.gdrive_warnings.length > 0) {
                        title = 'Partial Success';
                        text = 'Files were saved locally but there were Google Drive issues:';
                        icon = 'warning';
                        
                        let warningHtml = '<ul class="text-left mt-3">';
                        data.gdrive_warnings.forEach(warning => {
                            warningHtml += `<li>${warning}</li>`;
                        });
                        warningHtml += '</ul>';
                        
                        Swal.fire({
                            title: title,
                            html: text + warningHtml,
                            icon: icon,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload the page
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload the page
                            window.location.reload();
                        });
                    }
                })
                .catch(error => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to upload file: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        }
    });
    
    // Global edit function
    function editParameter(id, name) {
        console.log('Edit clicked for parameter:', id, name);
        document.getElementById('edit_parameter_name').value = name;
        
        const form = document.getElementById('editParameterForm');
        const url = `/parameters/${id}`;
        form.setAttribute('action', url);
        
        const modal = new bootstrap.Modal(document.getElementById('editParameterModal'));
        modal.show();
    }
    
    // Global delete function
    function deleteParameter(id) {
        console.log('Delete clicked for parameter:', id);
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch(`/parameters/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                })
                .then(response => {
                    // Check if the response status is in the successful range (200-299)
                    if (response.ok) {
                        return response.json().catch(() => {
                            // If it's not JSON, just return an empty object
                            return {};
                        });
                    }
                    throw new Error('Network response was not ok');
                })
                .then(data => {
                    Swal.fire(
                        'Deleted!',
                        'The parameter has been deleted.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Error!',
                        'Failed to delete parameter.',
                        'error'
                    );
                });
            }
        });
    }
    
    // Toggle parameter content
    function toggleParameterContent(contentId) {
        const content = document.getElementById(contentId);
        if (content) {
            if (content.style.display === 'none') {
                // Close all other open parameter contents first
                document.querySelectorAll('.parameter-content').forEach(item => {
                    if (item.id !== contentId) {
                        item.style.display = 'none';
                    }
                });
                
                // Animate opening
                content.style.display = 'block';
                content.style.maxHeight = '0';
                content.style.overflow = 'hidden';
                setTimeout(() => {
                    content.style.transition = 'max-height 0.5s ease-in-out';
                    content.style.maxHeight = content.scrollHeight + 'px';
                }, 10);
            } else {
                // Animate closing
                content.style.maxHeight = '0';
                setTimeout(() => {
                    content.style.display = 'none';
                    content.style.transition = '';
                    content.style.maxHeight = '';
                    content.style.overflow = '';
                }, 500);
            }
        }
    }
    
    // Function to add an indicator to a parameter
    function addIndicator(parameterId) {
        // Set the parameter ID in the hidden field
        document.getElementById('indicator_parameter_id').value = parameterId;
        
        // Set the form's action URL directly (not using route replacement)
        const form = document.getElementById('createIndicatorForm');
        form.action = '/parameters/' + parameterId + '/indicators';
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('addIndicatorModal'));
        modal.show();
        
        event.stopPropagation();
    }
    
    // Function to edit an indicator
    function editIndicator(indicatorId, indicatorName) {
        // Set values directly without fetch
        document.getElementById('edit_indicator_name').value = indicatorName || '';
        document.getElementById('editIndicatorForm').action = '/indicators/' + indicatorId;
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('editIndicatorModal'));
        modal.show();
        
        event.stopPropagation();
    }
    
    // Function to delete an indicator
    function deleteIndicator(indicatorId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Use fetch instead of jQuery AJAX
                fetch(`/indicators/${indicatorId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    // Check if the response status is in the successful range (200-299)
                    if (response.ok) {
                        return response.json().catch(() => {
                            // If it's not JSON, just return an empty object
                            return {};
                        });
                    }
                    throw new Error('Network response was not ok');
                })
                .then(data => {
                    Swal.fire(
                        'Deleted!',
                        'The indicator has been deleted.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Error!',
                        'Failed to delete indicator.',
                        'error'
                    );
                });
            }
        });
        
        event.stopPropagation();
    }
    
    // Function to upload file
    function uploadFile(indicatorId) {
        // Set the indicator ID in the hidden field
        document.getElementById('upload_indicator_id').value = indicatorId;
        
        // Set the form's action URL
        const form = document.getElementById('uploadFileForm');
        form.action = '/indicators/' + indicatorId + '/uploads';
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('uploadFileModal'));
        modal.show();
        
        event.stopPropagation();
    }
    
    // Function to view uploads
    function viewUploads(indicatorId) {
        // Fetch uploads for this indicator
        fetch('/indicators/' + indicatorId + '/uploads')
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error('Server responded with: ' + text);
                });
            }
            return response.json();
        })
        .then(data => {
            // Clear the container
            const container = document.getElementById('uploads-container');
            container.innerHTML = '';
            
            if (data.uploads && data.uploads.length > 0) {
                // Add each upload to the container
                data.uploads.forEach(upload => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    
                    // User avatar and name with tooltip behavior
                    const userHtml = upload.user ? 
                        `<div class="d-flex align-items-center me-3">
                            <div class="avatar avatar-sm position-relative" data-bs-toggle="tooltip" data-bs-placement="top" 
                                 title="${upload.user.name} (${upload.user.email})">
                                <img src="${upload.user.avatar || '/assets/img/default-avatar.png'}" 
                                     alt="${upload.user.name}" class="rounded-circle" width="30" height="30">
                            </div>
                            <span class="d-none d-md-inline ms-2">${upload.user.name}</span>
                        </div>` : '';
                    
                    // File info and actions
                    item.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file me-3 fa-lg text-primary"></i>
                            <div>
                                <div>${upload.description || 'No description'}</div>
                                <small class="text-muted">Uploaded ${new Date(upload.created_at).toLocaleDateString()}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            ${userHtml}
                            <a href="${upload.file_path}" class="btn btn-sm btn-outline-primary me-2" target="_blank">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            <a href="${upload.file_path}" class="btn btn-sm btn-outline-secondary" download>
                                <i class="fas fa-download me-1"></i> Download
                            </a>
                        </div>
                    `;
                    
                    container.appendChild(item);
                });
                
                // Initialize tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            } else {
                // No uploads found
                container.innerHTML = '<div class="text-center p-3">No files have been uploaded yet.</div>';
            }
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('viewUploadsModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Failed to fetch uploads: ' + error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
        
        event.stopPropagation();
    }
</script> 