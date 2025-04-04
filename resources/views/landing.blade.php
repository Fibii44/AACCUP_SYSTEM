<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ $settings->primary_color }};
            --secondary-color: {{ $settings->secondary_color }};
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            filter: brightness(90%);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .hero {
            background-color: var(--primary-color);
            color: white;
            padding: 5rem 0;
        }
        
        footer {
            background-color: var(--secondary-color);
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
            <h1>{{ $settings->header_text }}</h1>
            @if($settings->welcome_message)
                <p class="lead">{{ $settings->welcome_message }}</p>
            @endif
            <div class="mt-4">
                <a href="{{ route('login') }}" class="btn btn-light me-2">Login</a>
                <a href="{{ route('register') }}" class="btn btn-outline-light">Register</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h3 class="text-primary">Feature 1</h3>
                            <p>Describe your key feature here.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h3 class="text-primary">Feature 2</h3>
                            <p>Describe your key feature here.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h3 class="text-primary">Feature 3</h3>
                            <p>Describe your key feature here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section (conditionally shown) -->
    @if($settings->show_testimonials)
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">What Our Users Say</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="fst-italic">"This platform has transformed how we manage our processes."</p>
                            <p class="text-end fw-bold">- Jane Doe, Company A</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="fst-italic">"The best solution we've found for our needs."</p>
                            <p class="text-end fw-bold">- John Smith, Company B</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="fst-italic">"Excellent support and intuitive interface."</p>
                            <p class="text-end fw-bold">- Alice Brown, Company C</p>
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
            <p>{{ $settings->footer_text }}</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 