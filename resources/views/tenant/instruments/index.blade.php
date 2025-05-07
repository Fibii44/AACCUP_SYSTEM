@extends('layouts.dashboardTemplate')

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

    // Check if user has Google token
    $hasGoogleToken = auth()->user()->google_token !== null;
@endphp

<style>
    /* Override Bootstrap's default button styles */
    .btn-primary,
    .btn.bg-gradient-primary,
    .btn.bg-gradient-primary:not(:disabled):not(.disabled) {
        background: {{ $primaryColor }} !important;
        border-color: {{ $primaryColor }} !important;
        color: #fff !important;
    }

    .btn-primary:hover,
    .btn.bg-gradient-primary:hover:not(:disabled):not(.disabled) {
        background: {{ $primaryColor }} !important;
        border-color: {{ $primaryColor }} !important;
        opacity: 0.9;
    }

    .btn.bg-gradient-primary:disabled,
    .btn.bg-gradient-primary.disabled {
        background: {{ $primaryColor }} !important;
        border-color: {{ $primaryColor }} !important;
        opacity: 0.65;
    }

    /* Style for warning alert */
    .alert-warning {
        background-color: {{ $secondaryColor }}15 !important;
        border-color: {{ $secondaryColor }} !important;
        color: {{ $tertiaryColor }} !important;
    }

    .btn-warning {
        background-color: {{ $secondaryColor }} !important;
        border-color: {{ $secondaryColor }} !important;
        color: #fff !important;
    }
</style>

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
    
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if(auth()->user()->role === 'admin' && !$hasGoogleToken)
                            <div class="alert alert-warning mb-0 d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please connect your Google account first to create instruments.
                                <a href="{{ route('tenant.settings.profile') }}" class="btn btn-sm btn-warning ms-3">
                                    Connect Google Account
                                </a>
                            </div>
                            @endif
                        </div>
                        <div>
                            @if(auth()->user()->role === 'admin')
                            <button type="button" 
                                class="btn bg-gradient-primary {{ !$hasGoogleToken ? 'disabled' : '' }}" 
                                data-bs-toggle="modal" 
                                data-bs-target="{{ $hasGoogleToken ? '#createInstrumentModal' : '' }}"
                                {{ !$hasGoogleToken ? 'disabled' : '' }}>
                                + Instrument
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div id="instruments-container" class="p-3">
                        @foreach($instruments as $instrument)
                        <div class="card mb-3 instrument-card" data-id="{{ $instrument->id }}">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="drag-handle me-3" title="Drag to reorder">
                                            <i class="fas fa-grip-lines fa-lg"></i>
                                        </div>
                                        <a href="{{ route('tenant.instruments.show', $instrument->id) }}" class="text-decoration-none">
                                            <h5 class="mb-0">{{ $instrument->name }}</h5>
                                        </a>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm px-2 py-1 rounded" id="dropdownMenuButton-{{ $instrument->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-h fa-lg"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton-{{ $instrument->id }}">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('tenant.instruments.show', $instrument->id) }}">
                                                    <i class="fas fa-eye me-2"></i>View Details
                                                </a>
                                            </li>
                                            @if(auth()->user()->role === 'admin')
                                            <li>
                                                <a class="dropdown-item edit-instrument" href="#" data-id="{{ $instrument->id }}" data-name="{{ $instrument->name }}" onclick="editInstrument({{ $instrument->id }}, '{{ $instrument->name }}'); return false;">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger delete-instrument" href="#" data-id="{{ $instrument->id }}" onclick="deleteInstrument({{ $instrument->id }}); return false;">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->role === 'admin')
<!-- Create Instrument Modal -->
<div class="modal fade" id="createInstrumentModal" tabindex="-1" aria-labelledby="createInstrumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createInstrumentModalLabel">Create New Instrument</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createInstrumentForm" action="{{ route('tenant.instruments.store') }}" method="POST" onsubmit="return false;">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn bg-gradient-primary">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Instrument Modal -->
<div class="modal fade" id="editInstrumentModal" tabindex="-1" aria-labelledby="editInstrumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInstrumentModalLabel">Edit Instrument</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editInstrumentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <button type="submit" class="btn bg-gradient-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .instrument-card {
        cursor: move;
        transition: all 0.2s ease;
        border: 1px solid #eee;
    }
    .instrument-card:hover {
        border-color: #aaa;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .instrument-card.dragging {
        opacity: 0.7;
        background-color: #f8f9fa;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    .drag-handle {
        cursor: grab;
        padding: 10px 5px;
        margin: -10px 0;
        color: #aaa;
        font-size: 16px;
    }
    .drag-handle:hover {
        color: #666;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    .btn-outline-secondary {
        border: 1px solid #ced4da;
    }
    .dropdown-toggle::after {
        display: none;
    }
    .fa-ellipsis-h {
        font-size: 1.25rem;
        color: #6c757d;
    }
    .dropdown-menu-end {
        right: 0;
        left: auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
@endpush

<script>
    // Wait for the document to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Document ready');
        
        // Set up create form submission handler
        document.getElementById('createInstrumentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const url = form.getAttribute('action');
            const nameField = document.getElementById('name');
            const name = nameField.value;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...';
            submitBtn.disabled = true;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
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
                
                // Clear the form
                form.reset();
                
                // Close the modal
                bootstrap.Modal.getInstance(document.getElementById('createInstrumentModal')).hide();
                
                // Show success message
                Swal.fire({
                    title: 'Success!',
                    text: 'Instrument created successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                let errorMessage = 'Name already exists';
                
                // Handle validation errors
                if (error.status === 422 && error.data) {
                    if (error.data.errors && error.data.errors.name) {
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
        
        // Set up edit form submission handler
        document.getElementById('editInstrumentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const url = form.getAttribute('action');
            const nameField = document.getElementById('edit_name');
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
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editInstrumentModal')).hide();
                    Swal.fire({
                        title: 'Success!',
                        text: 'Instrument updated successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                let errorMessage = 'Name already exists';
                
                // Handle validation errors
                if (error.status === 422 && error.data) {
                    if (error.data.errors && error.data.errors.name) {
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
    });
    
    // Global edit function
    function editInstrument(id, name) {
        console.log('Edit clicked for instrument:', id, name);
        document.getElementById('edit_name').value = name;
        
        const form = document.getElementById('editInstrumentForm');
        const url = "{{ route('tenant.instruments.update', ':id') }}".replace(':id', id);
        form.setAttribute('action', url);
        
        const modal = new bootstrap.Modal(document.getElementById('editInstrumentModal'));
        modal.show();
    }
    
    // Global delete function
    function deleteInstrument(id) {
        console.log('Delete clicked for instrument:', id);
        
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
                
                fetch("{{ route('tenant.instruments.destroy', ':id') }}".replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire(
                        'Deleted!',
                        'The instrument has been deleted.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Error!',
                        'Failed to delete instrument.',
                        'error'
                    );
                });
            }
        });
    }
</script> 