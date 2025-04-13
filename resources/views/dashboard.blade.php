<x-app-layout>
    @php
    // Get the tenant settings
    $settings = \App\Models\TenantSetting::first() ?? new \App\Models\TenantSetting();
    
    // Get the colors with default fallbacks
    $primaryColor = $settings->primary_color ?? '#3490dc';
    $secondaryColor = $settings->secondary_color ?? '#6c757d';
    $tertiaryColor = $settings->tertiary_color ?? '#1a237e';
    @endphp

    <style>
        .text-indigo-600 {
            color: {{ $primaryColor }} !important;
        }
        
        .text-green-600 {
            color: {{ $secondaryColor }} !important;
        }
        
        .text-blue-600 {
            color: {{ $tertiaryColor }} !important;
        }
        
        .bg-green-50 {
            background-color: {{ $primaryColor }}15 !important;
        }
        
        .border-green-500 {
            border-color: {{ $primaryColor }} !important;
        }
        
        .text-green-700 {
            color: {{ $primaryColor }} !important;
        }
        
        .bg-red-50 {
            background-color: #FEE2E2 !important;
        }
        
        .text-gray-500 {
            color: {{ $tertiaryColor }}!important;
        }
        
        .font-semibold {
            font-weight: 600;
        }
        
        .bg-green-600, .hover\:bg-green-700:hover {
            background-color: {{ $primaryColor }} !important;
        }
        
        .bg-red-600, .hover\:bg-red-700:hover {
            background-color: #ef4444 !important;
        }
        
        button[type="submit"].bg-red-600:hover {
            background-color: #b91c1c !important;
        }
        
        .text-indigo-600, .hover\:text-indigo-500:hover {
            color: {{ $primaryColor }} !important;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5.5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Stats Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Pending Department Requests Card -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="mr-6 text-indigo-600">
                                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm font-medium px-2">Pending Department Requests</p>
                                <p class="text-4xl font-bold text-gray-500 mt-1 px-2">
                                    {{ \App\Models\TenantRequest::where('status', 'pending')->count() }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-8">
                            <a href="{{ route('admin.tenant-requests.index') }}" class="block w-full text-center mt-5 px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium" style="color: {{ $primaryColor }}; border-color: {{ $primaryColor }}30;">
                                View all
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Active Departments Card -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="mr-6 text-green-600">
                                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm font-medium px-2">Active Departments</p>
                                <p class="text-4xl font-bold text-gray-500 mt-1 px-2">
                                    {{ \App\Models\TenantRequest::where('status', 'approved')->count() }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-8">
                            <a href="#" class="block w-full text-center mt-5 px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium" style="color: {{ $secondaryColor }}; border-color: {{ $secondaryColor }}30;">
                                View all
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total Users Card -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="mr-6 text-blue-600">
                                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm font-medium px-2">Total Users</p>
                                <p class="text-4xl font-bold text-gray-500 mt-1 px-2">
                                    {{ \App\Models\User::count() }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-8">
                            <a href="#" class="block w-full text-center mt-5 px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium" style="color: {{ $tertiaryColor }}; border-color: {{ $tertiaryColor }}30;">
                                View all
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Department Registration Requests -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-500 p-6">Recent Department Registration Requests</h3>
                </div>
                
                @php
                    $recentRequests = \App\Models\TenantRequest::where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @if($recentRequests->isEmpty())
                    <div class="py-16 text-center">
                        <p class="text-gray-500 text-lg">No pending registration requests</p>
                    </div>
                @else
                    <div class="px-4 py-2">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="color: {{ $tertiaryColor }} !important;">Department</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="color: {{ $tertiaryColor }} !important;">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="color: {{ $tertiaryColor }} !important;">Domain</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="color: {{ $tertiaryColor }} !important;">Requested</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="color: {{ $tertiaryColor }} !important;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentRequests as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->department_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->domain }}.{{ config('app.domain') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->created_at->diffForHumans() }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex space-x-2">
                                                    <form action="{{ route('admin.tenant-requests.approve', $request) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700" style="background-color: {{ $primaryColor }} !important;">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" onclick="showRejectModal({{ $request->id }})" class="px-4 py-2 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">
                                                        Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if(\App\Models\TenantRequest::where('status', 'pending')->count() > 5)
                        <div class="px-6 py-4 border-t border-gray-200 text-center">
                            <a href="{{ route('admin.tenant-requests.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                View all pending requests
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    
    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-4" style="color: {{ $tertiaryColor }} !important;">Reject Department Request</h3>
            
            <form id="rejectForm" action="" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1" style="color: {{ $tertiaryColor }} !important;">
                        Reason for Rejection
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                              style="border-color: {{ $secondaryColor }}40; outline-color: {{ $primaryColor }};">
                    </textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideRejectModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Add custom styles for focus states
        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('textarea');
            const inputs = document.querySelectorAll('input');
            
            const primaryColor = '{{ $primaryColor }}';
            
            // Apply custom focus styles
            [...textareas, ...inputs].forEach(element => {
                element.addEventListener('focus', function() {
                    this.style.borderColor = primaryColor;
                    this.style.boxShadow = `0 0 0 3px ${primaryColor}30`;
                });
                
                element.addEventListener('blur', function() {
                    this.style.borderColor = '';
                    this.style.boxShadow = '';
                });
            });
        });
        
        function showRejectModal(requestId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            
            // Set the form action
            form.action = `/admin/tenant-requests/${requestId}/reject`;
            
            // Show the modal
            modal.classList.remove('hidden');
        }
        
        function hideRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('hidden');
        }
    </script>
</x-app-layout>
