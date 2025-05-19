@php
    $user = auth()->user();
@endphp

<form action="{{ route('profile.update') }}" method="POST">
    @csrf
    @method('PATCH')

    <div class="row">
        <!-- Profile Picture -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($user->profile_picture)
                            <img src="{{ $user->profile_picture }}" alt="Profile Picture" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 150px; height: 150px; background: {{ $primaryColor }}; color: white; font-size: 3rem;">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-control-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-control-label">Email Address</label>
                            <input type="email" class="form-control" id="email" value="{{ $user->email }}" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" style="background-color: {{ $primaryColor }}; border-color: {{ $primaryColor }};">
                                <i class="fas fa-user me-2"></i>Update Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form> 