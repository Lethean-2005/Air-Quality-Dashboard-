<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>503 - Service Unavailable</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('errors.partials.style')
</head>
<body>
    <div class="card">
        <div class="badge badge-hazardous">
            <img src="{{ asset('images/aqi-levels/aqi-hazardous-level.webp') }}" alt="">
        </div>
        <div class="code">503</div>
        <h1>Down for Maintenance</h1>
        <p>We're performing scheduled maintenance. Please check back shortly.</p>
        <a class="btn" href="{{ env('FRONTEND_URL', '/') }}">Back to Home</a>
    </div>
</body>
</html>
