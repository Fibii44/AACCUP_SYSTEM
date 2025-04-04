@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center pt-16 sm:pt-32">
    <div class="w-full max-w-md">
        <!-- Brand Logo/Name -->
        <div class="text-center mb-10">
            @if(isset($settings->logo_url) && $settings->logo_url)
                <img src="{{ $settings->logo_url }}" alt="Logo" class="h-16 mx-auto mb-4">
            @else
                <h1 class="text-3xl font-bold" style="color: {{ $settings->primary_color }}">
                    {{ explode('.', request()->getHost())[0] }}
                </h1>
            @endif
            <p class="text-gray-600 mt-2">Please sign in to your account</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Card Header -->
            <div class="px-6 py-4" style="background-color: {{ $settings->primary_color }}">
                <h2 class="text-xl font-semibold text-white">Login</h2>
            </div>

            <!-- Card Body -->
            <form method="POST" action="{{ route('tenant.login') }}" class="px-6 py-4">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                    
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" type="password" name="password" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                    
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                    </div>
                </div>

                <!-- Login Button -->
                <div class="flex items-center justify-between mt-6">
                    <button type="submit" class="text-white px-4 py-2 rounded-md" style="background-color: {{ $settings->primary_color }}">
                        Login
                    </button>

                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            Forgot your password?
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Additional Links -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                Don't have an account? Contact your administrator.
            </p>
        </div>
    </div>
</div>
@endsection 