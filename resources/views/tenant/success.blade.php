<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Submitted - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #000435;
            --highlight: #FFC100;
            --text: #FFFFFF;
        }
        .bg-buksu-primary { background-color: #000435; }
        .text-buksu-primary { color: #000435; }
        .border-buksu-primary { border-color: #000435; }
        .bg-buksu-highlight { background-color: #FFC100; }
        .text-buksu-highlight { color: #FFC100; }
        .border-buksu-highlight { border-color: #FFC100; }
        .btn-buksu {
            background-color: #FFC100;
            color: #000435;
            transition: all 0.3s ease;
        }
        .btn-buksu:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="antialiased bg-gray-50">
    <header class="bg-buksu-primary text-white">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <img src="https://buksu.edu.ph/wp-content/uploads/2024/03/cropped-Temp-Brand-Logo.png" alt="BukSU Logo" class="h-12">
                    <div class="text-xl font-bold">{{ config('app.name') }}</div>
                </div>
                <nav class="flex space-x-4">
                    <a href="{{ route('landing.index') }}" class="hover:text-buksu-highlight transition-colors duration-200">Home</a>
                    <a href="{{ route('landing.about') }}" class="hover:text-buksu-highlight transition-colors duration-200">About</a>
                    <a href="{{ route('landing.contact') }}" class="hover:text-buksu-highlight transition-colors duration-200">Contact</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-16">
        <div class="max-w-2xl mx-auto text-center">
            <div class="bg-white rounded-lg shadow-md p-8 border-t-4 border-buksu-highlight">
                <div class="text-buksu-highlight mb-4">
                    <i class="fas fa-check-circle h-16 w-16 mx-auto text-5xl"></i>
                </div>
                
                <h1 class="text-3xl font-bold mb-4 text-buksu-primary">Registration Submitted Successfully!</h1>
                
                <p class="text-gray-600 mb-6">
                    Thank you for registering your department. Your request has been submitted and is pending approval by the administrator.
                </p>
                
                <div class="bg-gray-50 border-l-4 border-buksu-highlight p-4 text-left mb-6">
                    <div class="text-buksu-primary font-medium">What happens next?</div>
                    <ul class="mt-2 list-inside text-sm text-gray-600">
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check-circle text-buksu-highlight mt-1"></i>
                            <span>Your request will be reviewed by the system administrator</span>
                        </li>
                        <li class="flex items-start space-x-2 mt-2">
                            <i class="fas fa-check-circle text-buksu-highlight mt-1"></i>
                            <span>Upon approval, you will receive an email with login credentials</span>
                        </li>
                        <li class="flex items-start space-x-2 mt-2">
                            <i class="fas fa-check-circle text-buksu-highlight mt-1"></i>
                            <span>You can then access your department's portal and customize it</span>
                        </li>
                    </ul>
                </div>
                
                <div class="mt-8">
                    <a href="{{ route('landing.index') }}" class="btn-buksu font-bold py-3 px-6 rounded-lg inline-flex items-center">
                        <i class="fas fa-home mr-2"></i>
                        Return to Home
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-buksu-primary text-white py-8 mt-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0 flex items-center">
                    <img src="https://buksu.edu.ph/wp-content/uploads/2024/03/cropped-Temp-Brand-Logo.png" alt="BukSU Logo" class="h-10 mr-3">
                    <div>
                        <p class="font-bold">{{ config('app.name') }}</p>
                        <p class="text-sm opacity-75">&copy; {{ date('Y') }}. All rights reserved.</p>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="h-8 w-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center hover:bg-buksu-highlight hover:text-buksu-primary transition-colors duration-200">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="h-8 w-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center hover:bg-buksu-highlight hover:text-buksu-primary transition-colors duration-200">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="h-8 w-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center hover:bg-buksu-highlight hover:text-buksu-primary transition-colors duration-200">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html> 