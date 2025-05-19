<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register Department - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #000435;
            --highlight: #FFC100;
            --text: #FFFFFF;
        }
        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .bg-highlight { background-color: var(--highlight); }
        .text-highlight { color: var(--highlight); }
        .btn-highlight {
            background-color: var(--highlight);
            color: var(--primary);
            transition: all 0.3s ease;
        }
        .btn-highlight:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .form-input:focus {
            border-color: var(--highlight);
            box-shadow: 0 0 0 3px rgba(255, 193, 0, 0.2);
        }
    </style>
</head>
<body class="antialiased bg-gray-50">
    <header class="bg-primary text-white shadow-lg">
        <div class="container mx-auto px-6 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <img src="https://buksu.edu.ph/wp-content/uploads/2024/03/cropped-Temp-Brand-Logo.png" alt="BukSU Logo" class="h-12">
                    <div class="text-xl font-bold">{{ config('app.name') }}</div>
                </div>
                <nav class="hidden md:flex space-x-6">
                    <a href="{{ route('landing.index') }}" class="hover:text-highlight transition-colors duration-200 font-medium">Home</a>
                    <a href="{{ route('landing.about') }}" class="hover:text-highlight transition-colors duration-200 font-medium">About</a>
                    <a href="{{ route('landing.contact') }}" class="hover:text-highlight transition-colors duration-200 font-medium">Contact</a>
                </nav>
                <div class="md:hidden">
                    <button class="text-white focus:outline-none">
                        <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24">
                            <path d="M4 5h16a1 1 0 0 1 0 2H4a1 1 0 1 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 0 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 0 1 0-2z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-primary mb-2">Register Your Department</h1>
                <div class="w-20 h-1 bg-highlight mx-auto rounded-full"></div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-8 border-t-4 border-highlight">
                <div class="flex items-center mb-6 text-primary">
                    <i class="fas fa-university text-highlight text-2xl mr-3"></i>
                    <p class="text-gray-600">
                        Complete the form below to register your department. Once submitted, your request will be reviewed by the administrator.
                    </p>
                </div>
                
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            <div class="text-red-700 font-medium">Please correct the following errors:</div>
                        </div>
                        <ul class="mt-2 list-disc list-inside text-sm text-red-600 ml-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('tenant.request.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-5">
                        <label for="department_name" class="block text-primary font-medium mb-2">Department Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <i class="fas fa-building"></i>
                            </div>
                            <input type="text" name="department_name" id="department_name" value="{{ old('department_name') }}" 
                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none form-input"
                                required>
                        </div>
                        <p class="text-sm text-gray-500 mt-1 ml-1">Enter the full name of your department</p>
                    </div>
                    
                    <div class="mb-5">
                        <label for="email" class="block text-primary font-medium mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" 
                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none form-input"
                                required>
                        </div>
                        <p class="text-sm text-gray-500 mt-1 ml-1">This email will be used for administrator access</p>
                    </div>
                    
                    <div class="mb-8">
                        <label for="domain" class="block text-primary font-medium mb-2">Domain Name</label>
                        <div class="flex items-center">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <input type="text" name="domain" id="domain" value="{{ old('domain') }}" 
                                    class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none form-input"
                                    required
                                    pattern="[a-z0-9][a-z0-9-]*[a-z0-9]">
                            </div>
                            <span class="ml-2 text-gray-600 font-medium">.{{ config('app.domain', 'example.com') }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1 ml-1">Use only lowercase letters, numbers, and hyphens (e.g., computer-science)</p>
                    </div>
                    
                    <div class="mt-8">
                        <button type="submit" class="w-full btn-highlight py-3 px-4 rounded-lg text-lg font-bold shadow-lg flex items-center justify-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Submit Registration Request
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="mt-8 text-center">
                <a href="{{ route('landing.index') }}" class="inline-flex items-center text-primary hover:text-highlight transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to Home
                </a>
            </div>
        </div>
    </main>

    <footer class="bg-primary text-white py-10 mt-16">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0 flex items-center">
                    <img src="https://buksu.edu.ph/wp-content/uploads/2024/03/cropped-Temp-Brand-Logo.png" alt="BukSU Logo" class="h-10 mr-3">
                    <div>
                        <p class="font-bold">{{ config('app.name') }}</p>
                        <p class="text-sm opacity-75">&copy; {{ date('Y') }}. All rights reserved.</p>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="h-10 w-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center hover:bg-highlight hover:text-primary transition-colors duration-200">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="h-10 w-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center hover:bg-highlight hover:text-primary transition-colors duration-200">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="h-10 w-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center hover:bg-highlight hover:text-primary transition-colors duration-200">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html> 