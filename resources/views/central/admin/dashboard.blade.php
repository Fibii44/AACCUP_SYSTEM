<x-app-layout>
    <style>
        :root {
            --primary: #000435;
            --highlight: #FFC100;
            --text-light: #FFFFFF;
        }
        
        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .bg-highlight { background-color: var(--highlight); }
        .text-highlight { color: var(--highlight); }
        .border-highlight { border-color: var(--highlight); }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: var(--highlight);
        }
        
        .btn-highlight {
            background-color: var(--highlight);
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .btn-highlight:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .stats-icon {
            background-color: rgba(0, 4, 53, 0.1);
            color: var(--primary);
            border-radius: 50%;
            padding: 10px;
        }
        
        .section-title {
            position: relative;
            padding-left: 15px;
        }
        
        .section-title::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: var(--highlight);
            border-radius: 4px;
        }
    </style>

    <div class="py-12 bg-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-md shadow-sm">
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
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md shadow-sm">
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Pending Department Requests Card -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden card-hover border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="stats-icon mr-5">
                                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-primary text-sm font-medium">Pending Department Requests</p>
                                <p class="text-4xl font-bold text-primary mt-1">
                                    {{ \App\Models\TenantRequest::where('status', 'pending')->count() }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('admin.tenant-requests.index') }}" class="block w-full text-center px-4 py-2 border border-highlight rounded-lg text-primary bg-white hover:bg-highlight hover:text-primary transition-colors duration-300 text-sm font-medium">
                                View all
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Active Departments Card -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden card-hover border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="stats-icon mr-5">
                                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-primary text-sm font-medium">Active Departments</p>
                                <p class="text-4xl font-bold text-primary mt-1">
                                    {{ \App\Models\TenantRequest::where('status', 'approved')->count() }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <a href="#" class="block w-full text-center px-4 py-2 border border-highlight rounded-lg text-primary bg-white hover:bg-highlight hover:text-primary transition-colors duration-300 text-sm font-medium">
                                View all
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total Users Card -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden card-hover border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="stats-icon mr-5">
                                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-primary text-sm font-medium">Total Users</p>
                                <p class="text-4xl font-bold text-primary mt-1">
                                    {{ \App\Models\User::count() }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <a href="#" class="block w-full text-center px-4 py-2 border border-highlight rounded-lg text-primary bg-white hover:bg-highlight hover:text-primary transition-colors duration-300 text-sm font-medium">
                                View all
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Upgrade Requests Section -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8 border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center bg-primary">
                    <h3 class="text-lg font-semibold text-white section-title">Pending Subscription Upgrade Requests</h3>
                    <span class="px-3 py-1 bg-highlight text-primary text-xs font-bold rounded-full">
                        {{ \App\Models\UpgradeRequest::where('status', 'pending')->count() }} Pending
                    </span>
                </div>
                
                @php
                    $pendingUpgradeRequests = \App\Models\UpgradeRequest::where('status', 'pending')
                        ->with('tenant')
                        ->orderBy('requested_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @if($pendingUpgradeRequests->isEmpty())
                    <div class="py-16 text-center">
                        <p class="text-primary text-lg">No pending upgrade requests</p>
                    </div>
                @else
                    <div class="px-4 py-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-primary uppercase tracking-wider">Department</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-primary uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-primary uppercase tracking-wider">Current Plan</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-primary uppercase tracking-wider">Requested</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-primary uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pendingUpgradeRequests as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">{{ $request->tenant->department_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">{{ $request->tenant->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $request->tenant->isPremium() ? 'bg-highlight text-primary' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $request->tenant->isPremium() ? 'Premium' : 'Free' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">{{ $request->requested_at->diffForHumans() }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex space-x-2">
                                                    <form action="{{ route('admin.upgrade-requests.approve', $request) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 bg-highlight text-primary text-xs font-medium rounded hover:bg-amber-400 transition-colors duration-300">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" onclick="showUpgradeRejectModal({{ $request->id }})" class="px-4 py-2 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors duration-300">
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
                    @if(\App\Models\UpgradeRequest::where('status', 'pending')->count() > 5)
                        <div class="px-6 py-4 border-t border-gray-200 text-center">
                            <a href="{{ route('admin.upgrade-requests.index') }}" class="text-sm font-medium text-highlight hover:text-primary transition-colors duration-300">
                                View all pending upgrade requests
                            </a>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Recent Department Registration Requests -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8 border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-200 bg-primary">
                    <h3 class="text-lg font-semibold text-white section-title">Recent Department Registration Requests</h3>
                </div>
                
                @php
                    $recentRequests = \App\Models\TenantRequest::where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @if($recentRequests->isEmpty())
                    <div class="py-16 text-center">
                        <p class="text-primary text-lg">No pending registration requests</p>
                    </div>
                @else
                    <div class="px-4 py-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-primary uppercase tracking-wider" >Department</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-primary uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-primary uppercase tracking-wider">Domain</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-primary uppercase tracking-wider">Requested</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-primary uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentRequests as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">{{ $request->department_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">{{ $request->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">{{ $request->domain }}.{{ config('app.domain') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">{{ $request->created_at->diffForHumans() }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex space-x-2">
                                                    <form action="{{ route('admin.tenant-requests.approve', $request) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 bg-highlight text-primary text-xs font-medium rounded hover:bg-amber-400 transition-colors duration-300">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" onclick="showTenantRejectModal({{ $request->id }})" class="px-4 py-2 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors duration-300">
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
                            <a href="{{ route('admin.tenant-requests.index') }}" class="text-sm font-medium text-highlight hover:text-primary transition-colors duration-300">
                                View all pending requests
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    
    <!-- Tenant Reject Modal -->
    <div id="tenantRejectModal" class="fixed inset-0 bg-primary bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md border-t-4 border-highlight">
            <h3 class="text-lg font-semibold text-primary mb-4">Reject Department Request</h3>
            
            <form id="tenantRejectForm" action="" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-primary mb-1">
                        Reason for Rejection
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-highlight focus:border-highlight sm:text-sm"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideTenantRejectModal()" class="px-4 py-2 border border-gray-300 rounded-md text-primary bg-white hover:bg-gray-50 text-sm font-medium transition-colors duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-highlight text-primary text-sm font-medium rounded hover:bg-amber-400 transition-colors duration-300">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Upgrade Reject Modal -->
    <div id="upgradeRejectModal" class="fixed inset-0 bg-primary bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md border-t-4 border-highlight">
            <h3 class="text-lg font-semibold text-primary mb-4">Reject Upgrade Request</h3>
            
            <form id="upgradeRejectForm" action="" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="upgrade_rejection_reason" class="block text-sm font-medium text-primary mb-1">
                        Reason for Rejection
                    </label>
                    <textarea id="upgrade_rejection_reason" name="rejection_reason" rows="4" required
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-highlight focus:border-highlight sm:text-sm"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideUpgradeRejectModal()" class="px-4 py-2 border border-gray-300 rounded-md text-primary bg-white hover:bg-gray-50 text-sm font-medium transition-colors duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-highlight text-primary text-sm font-medium rounded hover:bg-amber-400 transition-colors duration-300">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showTenantRejectModal(requestId) {
            const modal = document.getElementById('tenantRejectModal');
            const form = document.getElementById('tenantRejectForm');
            
            // Set the form action
            form.action = `/admin/tenant-requests/${requestId}/reject`;
            
            // Show the modal
            modal.classList.remove('hidden');
        }
        
        function hideTenantRejectModal() {
            const modal = document.getElementById('tenantRejectModal');
            modal.classList.add('hidden');
        }
        
        function showUpgradeRejectModal(requestId) {
            const modal = document.getElementById('upgradeRejectModal');
            const form = document.getElementById('upgradeRejectForm');
            
            // Set the form action
            form.action = `/admin/upgrade-requests/${requestId}/reject`;
            
            // Show the modal
            modal.classList.remove('hidden');
        }
        
        function hideUpgradeRejectModal() {
            const modal = document.getElementById('upgradeRejectModal');
            modal.classList.add('hidden');
        }
    </script>
</x-app-layout>
