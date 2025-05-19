<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About - {{ config('app.name') }}</title>
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
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-16">
            <div class="container mx-auto px-6 text-center">
                <h1 class="text-4xl font-extrabold leading-tight mb-4">
                    About Our Platform
                </h1>
                <p class="text-xl md:text-2xl max-w-3xl mx-auto">
                    Empowering educational institutions with efficient accreditation management
                </p>
            </div>
        </div>
        
        <div class="container mx-auto px-6 py-12">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-bold mb-6">Our Mission</h2>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    Our mission is to streamline the accreditation process for educational institutions through our innovative multi-database tenancy platform. We aim to reduce the administrative burden of accreditation while enhancing the quality and organization of documentation.
                </p>
                
                <h2 class="text-3xl font-bold mb-6 mt-12">Why We Built This</h2>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    Through our work with educational institutions, we recognized the challenges faced during accreditation processes. Documentation often became scattered, responsibilities unclear, and deadlines frequently missed. We developed this SaaS solution to address these pain points, providing a centralized yet customizable platform that respects each department's unique needs.
                </p>
                
                <h2 class="text-3xl font-bold mb-6 mt-12">Key Features</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-3">Multi-Database Architecture</h3>
                        <p class="text-gray-600">Each department has its own isolated database, ensuring data security and privacy while allowing for department-specific customizations.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-3">Customizable Interface</h3>
                        <p class="text-gray-600">Departments can customize their portal with their own branding, colors, and layout preferences to create a unique experience.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-3">Document Management</h3>
                        <p class="text-gray-600">Comprehensive document organization with versioning, categories, and search functionality to keep accreditation materials organized.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-3">Role-Based Access</h3>
                        <p class="text-gray-600">Define custom roles and permissions to ensure the right people have access to the right information.</p>
                    </div>
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