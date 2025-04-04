<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Dynamic Tenant Styles -->
    @if(isset($settings))
    <style>
        :root {
            --primary-color: {{ $settings->primary_color ?? '#3490dc' }};
            --secondary-color: {{ $settings->secondary_color ?? '#6c757d' }};
        }
        .bg-primary {
            background-color: var(--primary-color);
        }
        .text-primary {
            color: var(--primary-color);
        }
        .border-primary {
            border-color: var(--primary-color);
        }
        .bg-secondary {
            background-color: var(--secondary-color);
        }
    </style>
    @endif
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @if(tenant())
            <!-- Tenant Navigation -->
            <nav class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="flex-shrink-0 flex items-center">
                                @if(isset($settings->logo_url) && $settings->logo_url)
                                    <img src="{{ $settings->logo_url }}" alt="Logo" class="h-8 w-auto">
                                @else
                                    <span class="text-xl font-bold" style="color: {{ $settings->primary_color ?? '#3490dc' }}">
                                        {{ tenant()->id }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                @auth
                                    <a href="{{ route('tenant.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition">
                                        Dashboard
                                    </a>
                                @endauth
                            </div>
                        </div>
                        
                        <div class="hidden sm:ml-6 sm:flex sm:items-center">
                            @auth
                                <div class="ml-3 relative">
                                    <div>
                                        <form method="POST" action="{{ route('tenant.logout') }}">
                                            @csrf
                                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>
</body>
</html> 