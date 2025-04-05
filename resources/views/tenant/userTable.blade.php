@extends('layouts.dashboardTemplate')

@section('content')
<div class="row">
    <div class="col-12">
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
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date Added</th>        
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                <span class="badge badge-sm {{ $member->email_verified_at ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                    {{ $member->email_verified_at ? 'Active' : 'Pending' }}
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

                                    <!-- Delete Button -->
                                    <button type="button" class="btn btn-sm bg-gradient-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteFacultyModal"
                                            data-faculty-id="{{ $member->id }}"
                                            data-faculty-name="{{ $member->name }}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
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

<!-- Delete User Modal -->
<div class="modal fade" id="deleteFacultyModal" tabindex="-1" role="dialog" aria-labelledby="deleteFacultyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteFacultyModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="deleteFacultyForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete this user: <span id="deleteUserName" class="font-weight-bold"></span>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn bg-gradient-danger">Delete User</button>
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

    // Delete modal functionality
        const deleteFacultyModal = document.getElementById('deleteFacultyModal');
        const deleteForm = document.getElementById('deleteFacultyForm');
        const deleteUserNameSpan = document.getElementById('deleteUserName');

        document.querySelectorAll('[data-bs-target="#deleteFacultyModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const facultyId = this.getAttribute('data-faculty-id');
                const facultyName = this.getAttribute('data-faculty-name');
                
                // Update the form action URL
                deleteForm.action = `/faculty/${facultyId}`;
                
                // Update the user name in the confirmation message
                deleteUserNameSpan.textContent = facultyName;
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