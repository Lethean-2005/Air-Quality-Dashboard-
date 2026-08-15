<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>429 - Too Many Requests</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('errors.partials.style')
</head>
<body>
    <div class="card">
        <div class="badge badge-moderate">
            <img src="{{ asset('images/aqi-levels/aqi-moderate-level.webp') }}" alt="">
        </div>
        <div class="code">429</div>
        <h1>Too Many Requests</h1>
        <p>You've made too many requests in a short time. Please wait a moment and try again.</p>
        <a class="btn" href="{{ env('FRONTEND_URL', '/') }}">Back to Home</a>
    </div>
</body>
</html>
