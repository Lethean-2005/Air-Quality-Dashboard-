<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>403 - Forbidden</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('errors.partials.style')
</head>
<body>
    <div class="card">
        <div class="badge badge-poor">
            <img src="{{ asset('images/aqi-levels/aqi-poor-level.webp') }}" alt="">
        </div>
        <div class="code">403</div>
        <h1>Access Forbidden</h1>
        <p>You don't have permission to access this resource.</p>
        <a class="btn" href="{{ env('FRONTEND_URL', '/') }}">Back to Home</a>
    </div>
</body>
</html>
