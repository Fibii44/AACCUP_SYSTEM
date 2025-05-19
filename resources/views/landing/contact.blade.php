<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us - {{ config('app.name') }}</title>
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
                    Contact Us
                </h1>
                <p class="text-xl md:text-2xl max-w-3xl mx-auto">
                    We're here to help with any questions about our accreditation management system
                </p>
            </div>
        </div>
        
        <div class="container mx-auto px-6 py-12">
            <div class="max-w-4xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div>
                        <h2 class="text-2xl font-bold mb-6">Send us a message</h2>
                        <form class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                                <input type="text" id="name" name="name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <input type="text" id="subject" name="subject" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                <textarea id="message" name="message" rows="5" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                            </div>
                            
                            <div>
                                <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div>
                        <h2 class="text-2xl font-bold mb-6">Contact Information</h2>
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Address</h3>
                                <p class="text-gray-600">
                                    123 Education Ave<br>
                                    Suite 456<br>
                                    College Town, ST 12345<br>
                                    United States
                                </p>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Email</h3>
                                <p class="text-gray-600">
                                    support@accreditation-system.com
                                </p>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Phone</h3>
                                <p class="text-gray-600">
                                    (123) 456-7890
                                </p>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Working Hours</h3>
                                <p class="text-gray-600">
                                    Monday - Friday: 9:00 AM - 5:00 PM<br>
                                    Saturday & Sunday: Closed
                                </p>
                            </div>
                        </div>
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