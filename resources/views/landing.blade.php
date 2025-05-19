<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ $settings->primary_color }};
            --secondary-color: {{ $settings->secondary_color }};
            --tertiary-color: {{ $settings->tertiary_color ?? '#f8f9fa' }};
        }
        
        /* Base styles for all themes */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            font-weight: 500;
            padding: 10px 20px;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            filter: brightness(90%);
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }
        
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 8rem 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Pink theme special overrides */
        @if($settings->primary_color == '#ff85a9')
        body {
            background-color: #fff;
            color: #000;
        }
        
        .hero {
            background-color: #ff85a9;
            background-image: none;
            padding: 6rem 0;
        }
        
        .hero h1 {
            color: white !important;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .section-title {
            color: black;
        }
        
        .testimonials-section h2 {
            color: #ff85a9;
        }
        @endif
        
        /* Nursing/Red Palette */
        @if($settings->primary_color == '#EF4444' && $settings->secondary_color == '#B91C1C' && $settings->tertiary_color == '#FCA5A5')
        .hero {
            background: linear-gradient(135deg, #EF4444 0%, #B91C1C 100%);
        }
        
        .testimonials-section {
            background-color: #FECACA;
        }
        
        .section-title {
            color: #B91C1C;
        }
        @endif
        
        /* Technologies/Orange Palette */
        @if($settings->primary_color == '#F97316' && $settings->secondary_color == '#FB923C' && $settings->tertiary_color == '#FFEDD5')
        .hero {
            background: linear-gradient(135deg, #F97316 0%, #FB923C 100%);
        }
        
        .section-title {
            color: #7C2D12;
        }
        
        .testimonials-section {
            background-color: #FFEDD5;
        }
        @endif
        
        /* Modern Purple Palette */
        @if($settings->primary_color == '#6366F1' && $settings->secondary_color == '#8B5CF6' && $settings->tertiary_color == '#EC4899')
        .hero {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
        }
        
        .testimonial-card {
            border-top-color: #EC4899;
        }
        
        .section-title {
            color: #4338CA;
        }
        @endif
        
        /* Blue Ocean Palette */
        @if($settings->primary_color == '#0EA5E9' && $settings->secondary_color == '#38BDF8' && $settings->tertiary_color == '#0C4A6E')
        .hero {
            background: linear-gradient(135deg, #0EA5E9 0%, #38BDF8 100%);
        }
        
        .section-title, .testimonials-section h2 {
            color: #0C4A6E;
        }
        
        .testimonials-section {
            background-color: #E0F2FE;
        }
        @endif
        
        /* Emerald Green Palette */
        @if($settings->primary_color == '#059669' && $settings->secondary_color == '#10B981' && $settings->tertiary_color == '#064E3B')
        .hero {
            background: linear-gradient(135deg, #059669 0%, #10B981 100%);
        }
        
        .section-title, .testimonials-section h2 {
            color: #064E3B;
        }
        
        .testimonials-section {
            background-color: #D1FAE5;
        }
        @endif
        
        /* Pink-White-Black specific adjustments */
        @if($settings->primary_color == '#ff85a9' && $settings->secondary_color == '#ffffff' && $settings->tertiary_color == '#000000')
        .hero {
            background: var(--primary-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .hero p {
            color: white;
            opacity: 0.9;
        }
        
        .testimonial-card {
            border-top: 3px solid var(--primary-color);
        }
        @endif
        
        /* Regular styles continue below */
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }
        
        .btn-tertiary {
            background-color: var(--tertiary-color);
            border-color: var(--tertiary-color);
            color: black;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .text-secondary {
            color: var(--secondary-color) !important;
        }
        
        .text-tertiary {
            color: var(--tertiary-color) !important;
        }
        
        .bg-secondary {
            background-color: var(--secondary-color) !important;
        }
        
        .bg-tertiary {
            background-color: var(--tertiary-color) !important;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .logo-container {
            background-color: white;
            border-radius: 50%;
            padding: 15px;
            display: inline-block;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .hero h1 {
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 3rem;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .hero p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Feature card styling - consolidated */
        .feature-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-card .card-header {
            padding: 2rem 1rem;
            text-align: center;
            border-bottom: none;
        }
        
        .feature-card .card-body {
            padding: 2rem 1.5rem;
        }
        
        .feature-card-primary .card-header {
            background-color: var(--primary-color);
            color: white;
        }
        
        .feature-card-secondary .card-header {
            background-color: var(--secondary-color);
            color: var(--tertiary-color);
            border-bottom: 3px solid var(--primary-color);
        }
        
        .feature-card-secondary .card-header i {
            color: var(--primary-color);
        }
        
        .feature-card-tertiary .card-header {
            background-color: var(--tertiary-color);
            color: white;
        }
        
        /* Section styling */
        .section-title {
            color: var(--tertiary-color);
            position: relative;
            margin-bottom: 3rem;
            font-weight: 600;
        }
        
        .section-title:after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
            margin-top: 15px;
        }
        
        /* Testimonials styling */
        .testimonials-section {
            background-color: #f8f9fa;
            color: var(--tertiary-color);
            padding: 5rem 0;
        }
        
        .testimonials-section h2 {
            color: var(--primary-color);
        }
        
        .testimonial-card {
            border-radius: 10px;
            border: none;
            border-top: 3px solid var(--primary-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .testimonial-icon {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .testimonial-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        
        footer {
            background: var(--primary-color);
            color: white;
            padding: 4rem 0 2rem;
            position: relative;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.3;
        }
        
        .footer-content {
            position: relative;
            z-index: 1;
        }
        
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .social-icon:hover {
            background-color: white;
            color: var(--primary-color) !important;
            transform: translateY(-3px);
        }
        
        /* Add any custom CSS from tenant settings */
        {!! $settings->custom_css ? json_encode($settings->custom_css) : '' !!}
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content text-center">
            @if($settings->logo_url)
                <div class="logo-container">
                    <img src="{{ $settings->logo_url }}" alt="Logo" class="img-fluid" style="max-height: 80px;">
                </div>
            @endif
            @if($settings->primary_color == '#ff85a9')
                <h1 class="display-4 fw-bold" style="color: white !important;">{{ $settings->header_text }}</h1>
                @if($settings->welcome_message)
                    <p class="lead mx-auto" style="color: white;">{{ $settings->welcome_message }}</p>
                @endif
                <div class="mt-5">
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4 me-3">
                        <i class="fas fa-sign-in-alt me-2" style="color: #ff85a9;"></i><span style="color: #ff85a9;">Login</span>
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
            @else
                <h1 class="display-4 fw-bold" style="color: white">{{ $settings->header_text }}</h1>
                @if($settings->welcome_message)
                    <p class="lead mx-auto">{{ $settings->welcome_message }}</p>
                @endif
                <div class="mt-5">
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4 me-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 mt-5" style="background-color: #fff;">
        <div class="container py-4">
            <h2 class="text-center section-title" style="color: {{ $settings->tertiary_color }}">Our Features</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card feature-card-primary h-100">
                        <div class="card-header">
                            <i class="fas fa-rocket fa-3x mb-3"></i>
                            <h3 class="fw-bold">Quick Setup</h3>
                        </div>
                        <div class="card-body text-center">
                            <p>Get your instance up and running in minutes with our streamlined onboarding process.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card feature-card-secondary h-100">
                        <div class="card-header" style="background-color: {{ $settings->secondary_color }}; color: {{ $settings->tertiary_color }}; border-bottom: 3px solid {{ $settings->primary_color }};">
                            <i class="fas fa-cogs fa-3x mb-3" style="color: {{ $settings->primary_color }}"></i>
                            <h3 class="fw-bold">Customizable</h3>
                        </div>
                        <div class="card-body text-center">
                            <p>Adapt the platform to match your brand with custom colors, logos, and content.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card feature-card-tertiary h-100">
                        <div class="card-header" style="background-color: {{ $settings->tertiary_color }}; color: white;">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <h3 class="fw-bold">Analytics</h3>
                        </div>
                        <div class="card-body text-center">
                            <p>Track progress and measure success with comprehensive reporting and analytics.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section (conditionally shown) -->
    @if($settings->show_testimonials)
    <section class="testimonials-section" style="background-color: #f8f9fa; color: {{ $settings->tertiary_color }}">
        <div class="container position-relative">
            <h2 class="text-center mb-5 fw-bold" style="color: {{ $settings->primary_color }}">What Our Users Say</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <i class="fas fa-quote-left testimonial-icon"></i>
                            <p class="testimonial-text">"This platform has transformed how we manage our processes. The interface is intuitive and the customization options are excellent."</p>
                            <div class="d-flex align-items-center">
                                <div class="testimonial-avatar bg-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="fw-bold mb-0 text-secondary">Jane Doe</p>
                                    <small class="text-muted">Company A</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <i class="fas fa-quote-left testimonial-icon"></i>
                            <p class="testimonial-text">"The best solution we've found for our needs. The support team is responsive and the platform is continuously improving."</p>
                            <div class="d-flex align-items-center">
                                <div class="testimonial-avatar bg-secondary">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="fw-bold mb-0 text-primary">John Smith</p>
                                    <small class="text-muted">Company B</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <i class="fas fa-quote-left testimonial-icon"></i>
                            <p class="testimonial-text">"Excellent support and intuitive interface. The platform has helped us streamline our workflow and improve productivity."</p>
                            <div class="d-flex align-items-center">
                                <div class="testimonial-avatar" style="background-color: var(--tertiary-color);">
                                    <i class="fas fa-user" style="color: #333;"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="fw-bold mb-0 text-tertiary">Alice Brown</p>
                                    <small class="text-muted">Company C</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Footer -->
    <footer>
        <div class="container footer-content">
            <div class="row">
                <div class="col-md-6 mx-auto text-center">
                    @if($settings->logo_url)
                        <img src="{{ $settings->logo_url }}" alt="Logo" class="img-fluid mb-4" style="max-height: 60px; filter: brightness(0) invert(1);">
                    @endif
                    <p class="mb-4">{{ $settings->footer_text }}</p>
                    <div class="mb-4">
                        <a href="#" class="social-icon text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-icon text-white">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="social-icon text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                    <p><small>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</small></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html> 