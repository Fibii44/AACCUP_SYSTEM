<x-app-layout>
    <!-- Add Alpine.js -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Add jQuery first -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Add CSS for dropdown positioning -->
    @push('styles')
    <style>
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-menu {
            position: absolute;
            right: 0;
            z-index: 50;
            margin-top: 0.5rem;
            transform-origin: top right;
        }
        
        .dropdown-menu.show {
            display: block;
            animation: dropdown-appear 0.2s ease-out;
        }
        
        @keyframes dropdown-appear {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .dataTables_wrapper .dataTables_length select {
            padding-right: 2.5rem;
            border-radius: 0.375rem;
            border-color: #D1D5DB;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .dataTables_wrapper .dataTables_filter input {
            padding: 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid #D1D5DB;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            margin-left: 0.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: 0.375rem;
            border: 1px solid #D1D5DB;
            background: white;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #EEF2FF;
            border-color: #6366F1;
            color: #4F46E5 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #F3F4F6;
            border-color: #9CA3AF;
        }

        .dataTables_wrapper .dataTables_info {
            padding-top: 1rem;
            font-size: 0.875rem;
            color: #6B7280;
        }
    </style>
    @endpush

    <!-- Add DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-500">Tenants</h3>
        </div>
        
        @php
            $tenants = \App\Models\Tenant::orderBy('created_at', 'desc')->get();
        @endphp
        
        @if($tenants->isEmpty())
            <div class="py-16 text-center">
                <p class="text-gray-500 text-lg">No tenants found</p>
            </div>
        @else
            <div class="px-4 py-2">
                <div class="overflow-x-auto">
                    <table id="tenantsTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dept</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Domain</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tenants as $tenant)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-4 text-sm text-gray-900 truncate max-w-[150px]" title="{{ $tenant->department_name }}">
                                        {{ $tenant->department_name }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 truncate max-w-[200px]" title="{{ $tenant->email }}">
                                        {{ $tenant->email }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 truncate max-w-[150px]" title="{{ $tenant->id }}.{{ config('app.domain') }}">
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-gray-900">{{ $tenant->id }}</span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tenant->is_domain_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $tenant->is_domain_enabled ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4">
                                        @if($tenant->plan === 'free')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Free
                                            </span>
                                        @else
                                            <div class="flex flex-col gap-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Premium
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tenant->is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $tenant->is_paid ? 'Paid' : 'Unpaid' }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $tenant->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-3 py-4 text-right">
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button @click="open = !open" type="button" class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 inline-flex items-center">
                                                Actions
                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            
                                            <div x-show="open" 
                                                 @click.away="open = false"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="transform opacity-0 scale-95"
                                                 x-transition:enter-end="transform opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="transform opacity-100 scale-100"
                                                 x-transition:leave-end="transform opacity-0 scale-95"
                                                 class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                                <div class="py-1" role="menu" aria-orientation="vertical">
                                                    @if($tenant->is_domain_enabled)
                                                        <form action="{{ route('admin.tenants.disable', $tenant) }}" method="POST" class="block">
                                                            @csrf
                                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50" role="menuitem">
                                                                Disable Domain
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.tenants.enable', $tenant) }}" method="POST" class="block">
                                                            @csrf
                                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50" role="menuitem">
                                                                Enable Domain
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($tenant->plan === 'free')
                                                        <form action="{{ route('admin.tenants.upgrade', $tenant) }}" method="POST" class="block">
                                                            @csrf
                                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50" role="menuitem">
                                                                Upgrade to Premium
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.tenants.downgrade', $tenant) }}" method="POST" class="block">
                                                            @csrf
                                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" role="menuitem">
                                                                Downgrade to Free
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
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

    <!-- Move DataTables JS before the initialization script -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#tenantsTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[4, 'desc']], // Sort by Created column by default
                language: {
                    search: "Search:",
                    lengthMenu: "_MENU_ per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "No entries to show",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                drawCallback: function() {
                    // Ensure dropdowns work properly after page changes
                    if (window.Alpine) {
                        Alpine.initTree(document.body);
                    }
                }
            });

            // Add custom classes to the search input
            $('.dataTables_filter input').addClass('focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400');
            $('.dataTables_length select').addClass('focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400');
        });
    </script>
    @endpush
</x-app-layout>