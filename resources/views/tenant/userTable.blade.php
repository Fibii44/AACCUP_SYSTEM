@extends('layouts.dashboardTemplate')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6>Users</h6>
                <!-- Add User Button -->
                <button type="button" class="btn bg-gradient-primary" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
                    <i class="fas fa-plus me-2"></i>Add User
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

<!-- Success Message -->
@if(session('success'))
<div class="position-fixed bottom-1 end-1 z-index-2">
    <div class="toast fade show p-2 bg-white" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header border-0">
            <i class="fas fa-check-circle text-success me-2"></i>
            <span class="me-auto font-weight-bold">Success</span>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <hr class="horizontal dark m-0">
        <div class="toast-body">
            {{ session('success') }}
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset form when modal is closed
    const addFacultyModal = document.getElementById('addFacultyModal');
    addFacultyModal.addEventListener('hidden.bs.modal', function () {
        const form = this.querySelector('form');
        form.reset();
    });

    // Auto-hide toast after 5 seconds
    const toast = document.querySelector('.toast');
    if (toast) {
        setTimeout(function() {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.hide();
        }, 5000);
    }
});
</script>
@endsection