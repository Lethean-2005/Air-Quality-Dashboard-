<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Air Quality Dashboard API</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('errors.partials.style')
</head>
<body>
    <div class="card">
        <div class="badge badge-good">
            <img src="{{ asset('images/aqi-levels/aqi-good-level.webp') }}" alt="">
        </div>
        <div class="code">200 &middot; OK</div>
        <h1>API is Running</h1>
        <p>This is the backend for the Air Quality Dashboard. There's nothing to see here directly — head to the app instead.</p>
        <a class="btn" href="{{ env('FRONTEND_URL', '/') }}">Open the App</a>
    </div>
</body>
</html>
