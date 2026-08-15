<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>500 - Server Error</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('errors.partials.style')
</head>
<body>
    <div class="card">
        <div class="badge badge-hazardous">
            <img src="{{ asset('images/aqi-levels/aqi-hazardous-level.webp') }}" alt="">
        </div>
        <div class="code">500</div>
        <h1>Something Went Wrong</h1>
        <p>An unexpected error occurred on our end. We're looking into it — please try again shortly.</p>
        <a class="btn" href="{{ env('FRONTEND_URL', '/') }}">Back to Home</a>
    </div>
</body>
</html>
