@extends('layouts.app')

@section('content')
<style>
    .google-btn {
        width: 100%;
        height: 42px;
        background-color: #4285f4;
        border-radius: 4px;
        box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        transition: box-shadow 0.3s ease;
        text-decoration: none;
    }
    
    .google-btn:hover {
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.25);
        background-color: #4285f4;
    }
    
    .google-btn:active {
        background-color: #3367d6;
    }
    
    .google-icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 2px;
        background-color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-left: 1px;
    }
    
    .google-icon {
        width: 18px;
        height: 18px;
    }
    
    .btn-text {
        color: #fff;
        font-size: 14px;
        letter-spacing: 0.2px;
        margin: 0 auto;
        padding-right: 12px;
    }
</style>
<div class="main-content mt-0">
    <section>
        <div class="page-header min-vh-75">
            <div class="container">
                <div class="row">
                    <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                        <div class="card card-plain mt-8">
                            <div class="card-header pb-0 text-start bg-transparent">
                                @if($settings->logo_url)
                                <div class="text-center mb-3">
                                    <img src="{{ $settings->logo_url }}" alt="Logo" class="img-fluid" style="max-height: 70px;">
                                </div>
                                @endif
                                <h3 class="font-weight-bolder" style="color: {{ $settings->primary_color }}">{{ $settings->header_text }}</h3>
                                <p class="mb-0 text-muted">Enter your email and password to sign in</p>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('tenant.login') }}" role="form">
                                    @csrf
                                    <label>Email</label>
                                    <div class="mb-3">
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" aria-label="Email" aria-describedby="email-addon" value="{{ old('email') }}" required>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <label>Password</label>
                                    <div class="mb-3">
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" aria-label="Password" aria-describedby="password-addon" required>
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">Remember me</label>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">Sign in</button>
                                    </div>
                                </form>
                                <div class="text-center">
                                    <p class="text-muted mt-3 mb-2">- OR -</p>
                                    <a href="{{ route('tenant.login.google') }}" class="google-btn mt-2 mb-3">
                                        <div class="google-icon-wrapper">
                                            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" class="google-icon">
                                                <g>
                                                    <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285f4"/>
                                                    <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34a853"/>
                                                    <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#fbbc05"/>
                                                    <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#ea4335"/>
                                                </g>
                                            </svg>
                                        </div>
                                        <p class="btn-text"><b>Sign in with Google</b></p>
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                <p class="mb-4 text-sm mx-auto">
                                    Don't have an account?
                                    <a href="javascript:;" class="text-info text-gradient font-weight-bold">Contact your administrator</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                            <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('../assets/img/curved-images/homebg2.jpg')"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection