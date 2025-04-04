@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-4">Tenant Dashboard</h1>
            <p class="text-gray-600 mb-6">Welcome to your tenant dashboard! This is where you'll manage your department's resources.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-100">
                    <h2 class="text-lg font-semibold text-blue-700 mb-2">Manage Content</h2>
                    <p class="text-gray-600 mb-4">Update your department's information and content.</p>
                    <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Get Started →</a>
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg border border-green-100">
                    <h2 class="text-lg font-semibold text-green-700 mb-2">User Management</h2>
                    <p class="text-gray-600 mb-4">Add or remove users from your department.</p>
                    <a href="#" class="text-green-600 hover:text-green-800 font-medium">Manage Users →</a>
                </div>
                
                <div class="bg-purple-50 p-6 rounded-lg border border-purple-100">
                    <h2 class="text-lg font-semibold text-purple-700 mb-2">Customize Landing Page</h2>
                    <p class="text-gray-600 mb-4">Update your department's landing page appearance.</p>
                    <a href="{{ route('tenant.landing-settings') }}" class="text-purple-600 hover:text-purple-800 font-medium">Customize →</a>
                </div>
            </div>
            
            <div class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-100">
                <h2 class="text-lg font-semibold mb-4">Recent Activity</h2>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                        <p class="text-gray-600">Your landing page was updated 2 days ago</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                        <p class="text-gray-600">New user joined your department</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                        <p class="text-gray-600">Content update was published</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 