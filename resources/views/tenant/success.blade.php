<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Submitted - {{ config('app.name') }}</title>
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

    <main class="container mx-auto px-6 py-16">
        <div class="max-w-2xl mx-auto text-center">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="text-green-500 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold mb-4">Registration Submitted Successfully!</h1>
                
                <p class="text-gray-600 mb-6">
                    Thank you for registering your department. Your request has been submitted and is pending approval by the administrator.
                </p>
                
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 text-left mb-6">
                    <div class="text-blue-700 font-medium">What happens next?</div>
                    <ul class="mt-2 list-disc list-inside text-sm text-gray-600">
                        <li>Your request will be reviewed by the system administrator</li>
                        <li>Upon approval, you will receive an email with login credentials</li>
                        <li>You can then access your department's portal and customize it</li>
                    </ul>
                </div>
                
                <div class="mt-8">
                    <a href="{{ route('landing.index') }}" class="bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Return to Home
                    </a>
                </div>
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