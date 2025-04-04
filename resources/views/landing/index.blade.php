<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Accreditation Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="antialiased">
    <header class="bg-blue-800 text-white">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold">{{ config('app.name') }}</div>
                <nav class="flex space-x-4">
                    <a href="{{ route('landing.index') }}" class="hover:underline">Home</a>
                    <a href="{{ route('landing.about') }}" class="hover:underline">About</a>
                    <a href="{{ route('landing.contact') }}" class="hover:underline">Contact</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:underline">Login</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-24">
            <div class="container mx-auto px-6 text-center">
                <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-4">
                    Streamline Your Accreditation Process
                </h1>
                <p class="text-xl md:text-2xl mb-8">
                    A comprehensive solution for managing college department accreditation documentation
                </p>
                <div class="mt-8">
                    <a href="{{ route('tenant.request') }}" class="bg-white text-blue-800 hover:bg-blue-100 font-bold py-3 px-6 rounded-lg shadow-lg">
                        Register Your Department
                    </a>
                </div>
            </div>
        </div>
        
        <div class="container mx-auto px-6 py-16">
            <h2 class="text-3xl font-bold text-center mb-8">Why Choose Our Platform?</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-3">Secure & Independent</h3>
                    <p class="text-gray-600">Each department has its own isolated database, ensuring data security and organization.</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-3">Customizable</h3>
                    <p class="text-gray-600">Tailor your department's portal with custom colors, backgrounds, and branding.</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-3">Scalable Solution</h3>
                    <p class="text-gray-600">Our platform grows with your needs, supporting multiple departments with varying requirements.</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('landing.about') }}" class="hover:underline">About</a>
                    <a href="{{ route('landing.contact') }}" class="hover:underline">Contact</a>
                    <a href="#" class="hover:underline">Privacy Policy</a>
                    <a href="#" class="hover:underline">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html> 