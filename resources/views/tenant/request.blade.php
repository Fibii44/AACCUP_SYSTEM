<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register Department - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="antialiased bg-gray-100">
    <header class="bg-blue-800 text-white">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold">{{ config('app.name') }}</div>
                <nav class="flex space-x-4">
                    <a href="{{ route('landing.index') }}" class="hover:underline">Home</a>
                    <a href="{{ route('landing.about') }}" class="hover:underline">About</a>
                    <a href="{{ route('landing.contact') }}" class="hover:underline">Contact</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-10">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-6 text-center">Register Your Department</h1>
            
            <div class="bg-white rounded-lg shadow-md p-8">
                <p class="mb-6 text-gray-600">
                    Complete the form below to register your department. Once submitted, your request will be reviewed by the administrator.
                </p>
                
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="text-red-700 font-medium">Please correct the following errors:</div>
                        <ul class="mt-2 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('tenant.request.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="department_name" class="block text-gray-700 font-medium mb-2">Department Name</label>
                        <input type="text" name="department_name" id="department_name" value="{{ old('department_name') }}" 
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                        <p class="text-sm text-gray-500 mt-1">Enter the full name of your department</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" 
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                        <p class="text-sm text-gray-500 mt-1">This email will be used for administrator access</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="domain" class="block text-gray-700 font-medium mb-2">Domain Name</label>
                        <div class="flex items-center">
                            <input type="text" name="domain" id="domain" value="{{ old('domain') }}" 
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                                pattern="[a-z0-9][a-z0-9-]*[a-z0-9]">
                            <span class="ml-2 text-gray-600">.{{ config('app.domain', 'example.com') }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Use only lowercase letters, numbers, and hyphens (e.g., computer-science)</p>
                    </div>
                    
                    <div class="mt-8">
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Submit Registration Request
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="mt-6 text-center">
                <a href="{{ route('landing.index') }}" class="text-blue-600 hover:underline">
                    ← Back to Home
                </a>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html> 