<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>419 - Page Expired</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('errors.partials.style')
</head>
<body>
    <div class="card">
        <div class="badge badge-moderate">
            <img src="{{ asset('images/aqi-levels/aqi-moderate-level.webp') }}" alt="">
        </div>
        <div class="code">419</div>
        <h1>Page Expired</h1>
        <p>Your session expired. Please go back and try again.</p>
        <a class="btn" href="{{ env('FRONTEND_URL', '/') }}">Back to Home</a>
    </div>
</body>
</html>
