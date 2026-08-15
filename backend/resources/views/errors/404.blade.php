<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>404 - Page Not Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('errors.partials.style')
</head>
<body>
    <div class="card">
        <div class="badge badge-poor">
            <img src="{{ asset('images/aqi-levels/aqi-poor-level.webp') }}" alt="">
        </div>
        <div class="code">404</div>
        <h1>Page Not Found</h1>
        <p>The page or endpoint you're looking for doesn't exist or may have been moved.</p>
        <a class="btn" href="{{ env('FRONTEND_URL', '/') }}">Back to Home</a>
    </div>
</body>
</html>
