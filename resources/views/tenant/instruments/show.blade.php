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
@endphp
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $instrument->name }}</h4>
                        <div>
                            @if(auth()->user()->role === 'admin')
                            <button type="button" class="btn btn-primary btn-sm me-2" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};" data-bs-toggle="modal" data-bs-target="#addAreaModal">
                                <i class="fas fa-plus"></i> Create Area
                            </button>
                            @endif
                            <a href="{{ route('tenant.instruments.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Drive Folder:</strong> 
                                @if($instrument->google_drive_folder_id)
                                    <i class="fas fa-check-circle text-success"></i> Created
                                @else
                                    <i class="fas fa-times-circle text-danger"></i> Not Created
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Areas Count:</strong> <span id="areas-count">{{ $instrument->areas->count() }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->role !== 'admin')
        <!-- Faculty User View Removed -->
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Areas</h5>
                    </div>
                </div>
                <div class="card-body p-3">
                    @if($instrument->areas->count() > 0)
                        <div id="areas-content" class="row g-3">
                            @foreach($instrument->areas as $area)
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header pb-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 text-sm">{{ $area->name }}</h6>
                                            <div>
                                                @if(auth()->user()->role === 'admin')
                                                <button type="button" class="btn btn-sm btn-info" onclick="editArea({{ $area->id }}, '{{ $area->name }}')">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteArea({{ $area->id }}, '{{ $area->name }}')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-info">{{ $area->parameters->count() }} Parameters</span>
                                                <span class="badge bg-success">{{ $area->parameters->flatMap->indicators->count() }} Indicators</span>
                                            </div>
                                            <div>
                                                <a href="{{ route('tenant.instruments.area.show', [$instrument->id, $area->id]) }}" class="btn btn-sm btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">
                                                    <i class="fas fa-sitemap me-1"></i> View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div id="no-areas-message" class="text-center py-4">
                            <p class="text-muted mb-0">No areas have been created for this instrument yet.</p>
                            <button type="button" class="btn btn-primary mt-3" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};" data-bs-toggle="modal" data-bs-target="#addAreaModal">
                                <i class="fas fa-plus me-1"></i> Create Your First Area
                            </button>
                        </div>
                    @endif
                    <div id="areas-loading" class="text-center py-3" style="display: none;">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Loading areas...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@if(auth()->user()->role === 'admin')
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
                    <button type="submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Add Area Modal -->
<div class="modal fade" id="addAreaModal" tabindex="-1" aria-labelledby="addAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAreaModalLabel">Create New Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAreaForm" onsubmit="createArea(event)">
                @csrf
                <input type="hidden" name="instrument_id" value="{{ $instrument->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="area_name" class="form-label">Area Name</label>
                        <input type="text" class="form-control" id="area_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">Create Area</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Area Modal -->
<div class="modal fade" id="editAreaModal" tabindex="-1" aria-labelledby="editAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAreaModalLabel">Edit Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAreaForm" method="POST" onsubmit="updateArea(event)">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_area_name" class="form-label">Area Name</label>
                        <input type="text" class="form-control" id="edit_area_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">Update Area</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Remove the global editInstrument function
    $(document).ready(function() {
        // Load areas for this instrument - No longer needed as we're rendering server-side
        // loadAreas();
        
        // Set up edit form submission handler
        const editForm = document.getElementById('editInstrumentForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
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
                .then(response => response.json())
                .then(data => {
                    bootstrap.Modal.getInstance(document.getElementById('editInstrumentModal')).hide();
                    Swal.fire({
                        title: 'Success!',
                        text: 'Instrument updated successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update instrument',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        }

        // Initialize the form submission
        $('#addAreaForm').submit(function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    // Close the modal
                    $('#addAreaModal').modal('hide');
                    
                    // Show success message and reload page
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Area created successfully!',
                        confirmButtonColor: '{{ $primaryColor }}'
                    }).then(() => {
                        window.location.reload(); // Reload the entire page instead of just updating areas
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while creating the area.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '{{ $primaryColor }}'
                    });
                }
            });
        });

        // Delete Area button handler
        $('.delete-area-btn').on('click', function() {
            const areaId = $(this).data('id');
            const areaName = $(this).data('name');
            
            Swal.fire({
                title: 'Delete Area?',
                text: `Are you sure you want to delete "${areaName}"? This action cannot be undone and will delete all associated parameters and indicators.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send delete request
                    $.ajax({
                        url: "{{ url('/tenant/areas') }}/" + areaId,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'The area has been deleted successfully.',
                                confirmButtonColor: '{{ $primaryColor }}'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while deleting the area.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage,
                                confirmButtonColor: '{{ $primaryColor }}'
                            });
                        }
                    });
                }
            });
        });
    });
    
    // We'll keep this function but it's not automatically called anymore
    function loadAreas() {
        // ... existing code ...
    }

    // Global function to delete an area
    function deleteArea(areaId, areaName) {
        console.log('Attempting to delete area:', areaId, areaName); // Debug logging
        
        Swal.fire({
            title: 'Delete Area?',
            text: `Are you sure you want to delete "${areaName}"? This action cannot be undone and will delete all associated parameters and indicators.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show a loading indicator
                Swal.fire({
                    title: 'Deleting...',
                    html: 'Please wait while we delete the area.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Get the CSRF token
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Prepare the URL for the delete request
                const url = "{{ route('tenant.areas.destroy', ['area' => ':areaId']) }}".replace(':areaId', areaId);
                
                console.log('Sending delete request to URL:', url);
                
                // Create form data for the request
                const formData = new FormData();
                formData.append('_token', token);
                formData.append('_method', 'DELETE');
                
                // Send the delete request
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
                            throw new Error(text);
                        });
                    }
                    
                    // Check if response is empty (no content)
                    if (response.status === 204 || response.headers.get('Content-Length') === '0') {
                        return { success: true };
                    }
                    
                    // Check Content-Type header to see if it's JSON
                    const contentType = response.headers.get('Content-Type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.text().then(text => {
                            if (!text || text.trim() === '') {
                                return { success: true };
                            }
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.warn('Failed to parse JSON response:', text);
                                return { success: true };
                            }
                        });
                    }
                    
                    // Default case - not JSON or empty
                    return { success: true };
                })
                .then(data => {
                    console.log('Success response:', data);
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'The area has been deleted successfully.',
                        confirmButtonColor: '{{ $primaryColor }}'
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while deleting the area: ' + error.message,
                        confirmButtonColor: '{{ $primaryColor }}'
                    });
                });
            }
        });
    }

    // Global function to edit an area
    function editArea(areaId, areaName) {
        console.log('Editing area:', areaId, areaName); // Debug logging
        
        // Set form values
        document.getElementById('edit_area_name').value = areaName;
        
        // Set form action URL - Fix to use the proper URL format for Laravel routes
        const form = document.getElementById('editAreaForm');
        form.action = "{{ route('tenant.areas.update', ['area' => ':areaId']) }}".replace(':areaId', areaId);
        
        // Store the area ID in a data attribute for later use
        form.dataset.areaId = areaId;
        
        // Show the modal using Bootstrap
        const modal = new bootstrap.Modal(document.getElementById('editAreaModal'));
        modal.show();
    }
    
    // Function to handle area update form submission
    function updateArea(event) {
        event.preventDefault();
        
        const form = document.getElementById('editAreaForm');
        const url = form.action;
        const areaName = document.getElementById('edit_area_name').value;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
        submitBtn.disabled = true;
        
        console.log('Updating area, sending to URL:', url);
        console.log('Area name:', areaName);
        
        // Create form data and append values
        const formData = new FormData();
        formData.append('_token', token);
        formData.append('_method', 'PUT');
        formData.append('name', areaName);
        
        // Use fetch instead of jQuery AJAX
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
                    throw new Error(text);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Success response:', data);
            
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('editAreaModal')).hide();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Area updated successfully!',
                confirmButtonColor: '{{ $primaryColor }}'
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the area: ' + error.message,
                confirmButtonColor: '{{ $primaryColor }}'
            });
        });
    }

    // Global function to create a new area
    function createArea(event) {
        event.preventDefault();
        
        const form = document.getElementById('addAreaForm');
        const formData = new FormData(form);
        const url = "{{ route('tenant.areas.store', ['instrument' => $instrument->id]) }}";
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...';
        submitBtn.disabled = true;
        
        console.log('Creating area, sending to URL:', url);
        
        // Use fetch to submit the form
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
                    throw new Error(text);
                });
            }
            
            // Check if response is empty
            if (response.status === 204 || response.headers.get('Content-Length') === '0') {
                return { success: true };
            }
            
            // Try to parse JSON
            const contentType = response.headers.get('Content-Type');
            if (contentType && contentType.includes('application/json')) {
                return response.text().then(text => {
                    if (!text || text.trim() === '') {
                        return { success: true };
                    }
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.warn('Failed to parse JSON response:', text);
                        return { success: true };
                    }
                });
            }
            
            return { success: true };
        })
        .then(data => {
            console.log('Success response:', data);
            
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Clear the form
            form.reset();
            
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('addAreaModal')).hide();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Area created successfully!',
                confirmButtonColor: '{{ $primaryColor }}'
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while creating the area: ' + error.message,
                confirmButtonColor: '{{ $primaryColor }}'
            });
        });
    }
</script>
@endsection