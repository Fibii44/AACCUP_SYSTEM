<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ $settings->primary_color ?? '#3490dc' }};
            --secondary-color: {{ $settings->secondary_color ?? '#6c757d' }};
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 650px;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 1rem;
            line-height: 1;
        }
        
        .error-message {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: #333;
        }
        
        .error-details {
            margin-bottom: 2rem;
            color: #666;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            filter: brightness(90%);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            @if(isset($settings->logo_url) && $settings->logo_url)
                <img src="{{ $settings->logo_url }}" alt="Logo" class="img-fluid mb-4" style="max-height: 80px;">
            @else
                <h2 class="mb-4" style="color: var(--primary-color);">{{ tenant('id') ?? 'Tenant' }}</h2>
            @endif
            
            <div class="error-code">404</div>
            <div class="error-message">Page Not Found</div>
            <div class="error-details">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
            </div>
            <a href="{{ route('landing') }}" class="btn btn-primary">Return to Home</a>
        </div>
    </div>
</body>
</html> 