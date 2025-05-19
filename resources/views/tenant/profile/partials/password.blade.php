<form action="{{ route('profile.password') }}" method="POST">
    @csrf
    @method('PATCH')

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="current_password" class="form-control-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-control-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="text-muted">Password must be at least 8 characters long</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-control-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">
                        <i class="fas fa-lock me-2"></i>Update Password
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@if(session('success'))
    <div class="alert alert-success mt-3">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mt-3">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif 