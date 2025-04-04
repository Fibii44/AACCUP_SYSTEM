<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            color: #3490dc;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-code">404</div>
            <div class="error-message">Page Not Found</div>
            <div class="error-details">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
            </div>
            <a href="{{ config('app.url') }}" class="btn btn-primary">Go to Homepage</a>
        </div>
    </div>
</body>
</html> 