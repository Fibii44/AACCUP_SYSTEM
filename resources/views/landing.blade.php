<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: {{ $settings->primary_color }};
            --secondary-color: {{ $settings->secondary_color }};
            --tertiary-color: {{ $settings->tertiary_color ?? '#f8f9fa' }};
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: white;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            filter: brightness(90%);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }
        
        .btn-tertiary {
            background-color: var(--tertiary-color);
            border-color: var(--tertiary-color);
            color: black;
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
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .bg-secondary {
            background-color: var(--secondary-color) !important;
        }
        
        .bg-tertiary {
            background-color: var(--tertiary-color) !important;
        }
        
        .hero {
            background-color: var(--primary-color);
            color: white;
            padding: 5rem 0;
        }
        
        .feature-card-primary .card-header {
            background-color: var(--primary-color);
            color: white;
        }
        
        .feature-card-secondary .card-header {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .feature-card-tertiary .card-header {
            background-color: var(--tertiary-color);
            color: black;
        }
        
        .testimonials-section {
            background-color: var(--secondary-color);
            color: white;
            padding: 3rem 0;
        }
        
        .testimonial-card {
            background-color: white;
            border-radius: 10px;
            border: none;
        }
        
        .testimonial-icon {
            color: var(--tertiary-color);
        }
        
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem 0;
        }
        
        /* Add any custom CSS from tenant settings */
        {!! $settings->custom_css ? json_encode($settings->custom_css) : '' !!}
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container text-center">
            @if($settings->logo_url)
                <img src="{{ $settings->logo_url }}" alt="Logo" class="img-fluid mb-4" style="max-height: 100px;">
            @endif
            <h1 class="display-4 fw-bold">{{ $settings->header_text }}</h1>
            @if($settings->welcome_message)
                <p class="lead">{{ $settings->welcome_message }}</p>
            @endif
            <div class="mt-4">
                <a href="{{ route('login') }}" class="btn btn-light me-2">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4 text-primary">Our Features</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 feature-card-primary">
                        <div class="card-header text-center">
                            <i class="fas fa-rocket fa-3x mb-3"></i>
                            <h3>Feature 1</h3>
                        </div>
                        <div class="card-body text-center">
                            <p>Describe your key feature here.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 feature-card-secondary">
                        <div class="card-header text-center">
                            <i class="fas fa-cogs fa-3x mb-3"></i>
                            <h3>Feature 2</h3>
                        </div>
                        <div class="card-body text-center">
                            <p>Describe your key feature here.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 feature-card-tertiary">
                        <div class="card-header text-center">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <h3>Feature 3</h3>
                        </div>
                        <div class="card-body text-center">
                            <p>Describe your key feature here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section (conditionally shown) -->
    @if($settings->show_testimonials)
    <section class="testimonials-section">
        <div class="container">
            <h2 class="text-center mb-4">What Our Users Say</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 testimonial-card">
                        <div class="card-body">
                            <i class="fas fa-quote-left fa-2x mb-3 testimonial-icon"></i>
                            <p class="fst-italic">"This platform has transformed how we manage our processes."</p>
                            <div class="d-flex align-items-center mt-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="fw-bold mb-0 text-secondary">Jane Doe</p>
                                    <small>Company A</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 testimonial-card">
                        <div class="card-body">
                            <i class="fas fa-quote-left fa-2x mb-3 testimonial-icon"></i>
                            <p class="fst-italic">"The best solution we've found for our needs."</p>
                            <div class="d-flex align-items-center mt-3">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="fw-bold mb-0 text-primary">John Smith</p>
                                    <small>Company B</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 testimonial-card">
                        <div class="card-body">
                            <i class="fas fa-quote-left fa-2x mb-3 testimonial-icon"></i>
                            <p class="fst-italic">"Excellent support and intuitive interface."</p>
                            <div class="d-flex align-items-center mt-3">
                                <div class="rounded-circle bg-tertiary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-dark"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="fw-bold mb-0 text-tertiary">Alice Brown</p>
                                    <small>Company C</small>
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
        <div class="container text-center">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    @if($settings->logo_url)
                        <img src="{{ $settings->logo_url }}" alt="Logo" class="img-fluid mb-3" style="max-height: 60px;">
                    @endif
                    <p>{{ $settings->footer_text }}</p>
                    <div class="mt-3">
                        <a href="#" class="text-tertiary me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-tertiary me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-tertiary me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-tertiary"><i class="fab fa-instagram"></i></a>
                    </div>
                    <p class="mt-3"><small>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</small></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 