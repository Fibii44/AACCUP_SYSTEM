<x-layouts.app>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">Department Registration Request</h1>
            <a href="{{ route('admin.tenant-requests.index') }}" class="text-blue-600 hover:underline flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Requests
            </a>
        </div>
        
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Request Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Department Name</p>
                        <p class="mt-1 text-md text-gray-900">{{ $tenantRequest->department_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Email Address</p>
                        <p class="mt-1 text-md text-gray-900">{{ $tenantRequest->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Domain Name</p>
                        <p class="mt-1 text-md text-gray-900">{{ $tenantRequest->domain }}.{{ config('app.domain') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Requested Date</p>
                        <p class="mt-1 text-md text-gray-900">{{ $tenantRequest->created_at->format('F d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="mt-1">
                            @if($tenantRequest->isPending())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif($tenantRequest->isApproved())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Approved
                                </span>
                            @elseif($tenantRequest->isRejected())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if($tenantRequest->isPending())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Approve Registration</h3>
                        <div class="text-sm text-gray-500 mb-4">
                            <p>Approving this request will:</p>
                            <ul class="list-disc pl-5 mt-2 space-y-1">
                                <li>Create a new tenant database</li>
                                <li>Set up the domain for this department</li>
                                <li>Generate a random password</li>
                                <li>Send login credentials to the provided email</li>
                            </ul>
                        </div>
                        <form action="{{ route('admin.tenant-requests.approve', $tenantRequest) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Approve Request
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Registration</h3>
                        <div class="text-sm text-gray-500 mb-4">
                            <p>Please provide a reason for rejecting this registration request. This reason will be included in the notification email sent to the applicant.</p>
                        </div>
                        <form action="{{ route('admin.tenant-requests.reject', $tenantRequest) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                                <textarea id="rejection_reason" name="rejection_reason" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" required></textarea>
                            </div>
                            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Reject Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app> 