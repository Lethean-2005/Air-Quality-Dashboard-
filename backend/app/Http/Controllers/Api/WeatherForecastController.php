<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherForecastController extends Controller
{
    /**
     * Real current + forecast conditions from OpenWeather (current weather + 5-day/3-hour
     * forecast — the finest granularity available on the free tier, so "hourly" here means
     * real 3-hour steps, not interpolated hours). Cached for 10 minutes per rounded
     * lat/lon so repeated tab switches don't re-hit the API.
     */
    public function index(Request $request)
    {
        $lat = round((float) $request->query('lat', 11.562108), 2);
        $lon = round((float) $request->query('lon', 104.888535), 2);
        $apiKey = config('services.openweather.api_key');

        if (!$apiKey) {
            return response()->json(['status' => 'error', 'message' => 'OpenWeather API key not configured'], 500);
        }

        $cacheKey = "weather_forecast_{$lat}_{$lon}";

        $payload = Cache::remember($cacheKey, 600, function () use ($lat, $lon, $apiKey) {
            $current = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => $lat, 'lon' => $lon, 'appid' => $apiKey, 'units' => 'metric',
            ]);
            $forecast = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/forecast', [
                'lat' => $lat, 'lon' => $lon, 'appid' => $apiKey, 'units' => 'metric',
            ]);

            if ($current->failed() || $forecast->failed()) {
                return null;
            }

            return ['current' => $current->json(), 'list' => $forecast->json('list') ?? []];
        });

        if (!$payload) {
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch weather forecast'], 502);
        }

        $current = $payload['current'];
        $list = collect($payload['list']);
        $today = date('Y-m-d');

        $todayEntries = $list->filter(fn ($e) => str_starts_with($e['dt_txt'], $today));
        $todayTemps = $todayEntries->pluck('main.temp');

        $hourly = $list->take(8)->map(fn ($e) => [
            'time' => $e['dt_txt'],
            'temp' => round($e['main']['temp']),
            'icon' => $e['weather'][0]['icon'] ?? null,
            'description' => $e['weather'][0]['description'] ?? null,
            'pop' => (int) round(($e['pop'] ?? 0) * 100),
        ])->values();

        $daily = $list->groupBy(fn ($e) => substr($e['dt_txt'], 0, 10))
            ->map(function ($entries, $date) {
                $temps = $entries->pluck('main.temp');
                $midday = $entries->sortBy(fn ($e) => abs((int) substr($e['dt_txt'], 11, 2) - 12))->first();
                return [
                    'date' => $date,
                    'min' => round($temps->min()),
                    'max' => round($temps->max()),
                    'icon' => $midday['weather'][0]['icon'] ?? null,
                    'description' => $midday['weather'][0]['description'] ?? null,
                    'pop' => (int) round(($entries->max('pop') ?? 0) * 100),
                ];
            })->values();

        return response()->json([
            'status' => 'ok',
            'current' => [
                'temp' => round($current['main']['temp']),
                'feels_like' => round($current['main']['feels_like']),
                'humidity' => $current['main']['humidity'],
                'description' => $current['weather'][0]['description'] ?? null,
                'icon' => $current['weather'][0]['icon'] ?? null,
                'temp_min_today' => $todayTemps->isNotEmpty() ? round($todayTemps->min()) : round($current['main']['temp_min']),
                'temp_max_today' => $todayTemps->isNotEmpty() ? round($todayTemps->max()) : round($current['main']['temp_max']),
                'pop_next' => (int) round(($list->first()['pop'] ?? 0) * 100),
                'wind_speed' => $current['wind']['speed'] ?? null, // m/s
                'wind_deg' => $current['wind']['deg'] ?? null,
                'wind_gust' => $current['wind']['gust'] ?? null, // m/s
                'clouds' => $current['clouds']['all'] ?? null, // %
                'visibility' => $current['visibility'] ?? null, // meters
                'pressure' => $current['main']['pressure'] ?? null, // hPa
                'rain_1h' => $current['rain']['1h'] ?? 0, // mm
            ],
            'hourly' => $hourly,
            'daily' => $daily,
        ]);
    }
}
