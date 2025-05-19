<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Accreditation Management System</title>
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
        .hover-highlight:hover { background-color: var(--highlight); color: var(--primary); }
        .btn-highlight {
            background-color: var(--highlight);
            color: var(--primary);
            transition: all 0.3s ease;
        }
        .btn-highlight:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .feature-card {
            transition: all 0.3s ease;
            border-left: 4px solid var(--highlight);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
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
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-highlight transition-colors duration-200 font-medium">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="bg-highlight text-primary hover:bg-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">Login</a>
                    @endauth
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

    <main>
        <div class="bg-primary text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://buksu.edu.ph/wp-content/uploads/2022/10/BSUDrone.jpg')] bg-cover bg-center opacity-10"></div>
            <div class="container mx-auto px-6 py-24 relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-6">
                        Streamline Your <span class="text-highlight">Accreditation</span> Process
                    </h1>
                    <p class="text-xl md:text-2xl mb-10 opacity-90">
                        A comprehensive solution for managing college department accreditation documentation
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('tenant.request') }}" class="btn-highlight font-bold py-3 px-8 rounded-lg text-lg shadow-lg inline-flex items-center justify-center">
                            <span>Register Your Department</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="{{ route('login') }}" class="border-2 hover:bg-white hover:text-primary font-bold py-3 px-8 rounded-lg text-lg transition-colors duration-200 inline-flex items-center justify-center" style="border-color: #FFC100; color: #FFC100;">
                            Sign In
                        </a>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="text-gray-50 fill-current">
                    <path d="M0,224L48,218.7C96,213,192,203,288,192C384,181,480,171,576,176C672,181,768,203,864,208C960,213,1056,203,1152,176C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>
        </div>
        
        <div class="container mx-auto px-6 py-16">
            <h2 class="text-3xl font-bold text-center mb-12 text-primary">Why Choose Our <span class="text-highlight">Platform</span>?</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="feature-card bg-white p-8 rounded-lg shadow-md">
                    <div class="text-highlight mb-4">
                        <i class="fas fa-shield-alt text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-primary">Secure & Independent</h3>
                    <p class="text-gray-600">Each department has its own isolated database, ensuring data security and organization.</p>
                </div>
                
                <div class="feature-card bg-white p-8 rounded-lg shadow-md">
                    <div class="text-highlight mb-4">
                        <i class="fas fa-sliders-h text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-primary">Customizable</h3>
                    <p class="text-gray-600">Tailor your department's portal with custom colors, backgrounds, and branding.</p>
                </div>
                
                <div class="feature-card bg-white p-8 rounded-lg shadow-md">
                    <div class="text-highlight mb-4">
                        <i class="fas fa-expand-arrows-alt text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-primary">Scalable Solution</h3>
                    <p class="text-gray-600">Our platform grows with your needs, supporting multiple departments with varying requirements.</p>
                </div>
            </div>

            <div class="mt-16 text-center">
                <h2 class="text-3xl font-bold text-primary mb-8">Trusted by <span class="text-highlight">Academic Institutions</span></h2>
                <p class="text-gray-600 max-w-2xl mx-auto mb-10">
                    Our accreditation management system is designed specifically for higher education institutions to 
                    streamline the complex process of program accreditation.
                </p>
                <a href="{{ route('landing.about') }}" class="inline-block text-primary hover:text-highlight font-medium transition-colors duration-200">
                    Learn more about our platform
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </main>

    <footer class="bg-primary text-white py-10">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0 flex items-center">
                    <img src="https://buksu.edu.ph/wp-content/uploads/2024/03/cropped-Temp-Brand-Logo.png" alt="BukSU Logo" class="h-10 mr-3">
                    <div>
                        <p class="font-bold">{{ config('app.name') }}</p>
                        <p class="text-sm opacity-75">&copy; {{ date('Y') }}. All rights reserved.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm mb-6 md:mb-0">
                    <a href="{{ route('landing.about') }}" class="hover:text-highlight transition-colors duration-200">About</a>
                    <a href="{{ route('landing.contact') }}" class="hover:text-highlight transition-colors duration-200">Contact</a>
                    <a href="#" class="hover:text-highlight transition-colors duration-200">Privacy Policy</a>
                    <a href="#" class="hover:text-highlight transition-colors duration-200">Terms of Service</a>
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