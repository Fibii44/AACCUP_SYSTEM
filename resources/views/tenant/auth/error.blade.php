@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center pt-16 sm:pt-32">
    <div class="w-full max-w-md">
        <!-- Brand Logo/Name -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-red-600">
                Authentication Error
            </h1>
            <p class="text-gray-600 mt-2">{{ $message ?? 'There was a problem with authentication' }}</p>
        </div>

        <!-- Error Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Card Header -->
            <div class="px-6 py-4 bg-red-600">
                <h2 class="text-xl font-semibold text-white">Error</h2>
            </div>

            <!-- Card Body -->
            <div class="px-6 py-4">
                <p class="text-gray-700 mb-4">
                    {{ $details ?? 'The system encountered an error while processing your authentication request. Please try again or contact support if the problem persists.' }}
                </p>

                <div class="flex items-center justify-between mt-6">
                    <a href="{{ route('landing') }}" class="bg-blue-500 text-white px-4 py-2 rounded-md">
                        Return to Home
                    </a>

                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">
                        Try Again
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 