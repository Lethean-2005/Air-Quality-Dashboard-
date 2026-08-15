<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IqairReading;
use App\Models\Station;
use Illuminate\Support\Facades\Cache;

class PollutionDataController extends Controller
{
    /**
     * All fields the frontend expects per station, read straight from the DB.
     * Populated on a schedule by `php artisan stations:sync` — the data itself only
     * changes every ~30 minutes, so these responses are cached (see CACHE_TTL below)
     * instead of re-querying/re-serializing ~19k rows on every single request, which
     * was taking 6-7 seconds per request against the remote DB. Sync commands clear
     * these keys on completion so fresh data shows up immediately post-sync rather
     * than waiting out the full TTL.
     */
    private const STATION_FIELDS = [
        'name', 'lat', 'lon', 'aqi', 'pm25', 'pm10', 'no2', 'co', 'o3', 'so2',
        'wind_speed', 'wind_dir', 'temperature', 'humidity', 'pressure', 'flag',
    ];

    private const CACHE_TTL_SECONDS = 15 * 60;

    public function getAqiData()
    {
        return response()->json(
            Cache::remember('pollution.aqi_data', self::CACHE_TTL_SECONDS, fn () => $this->buildAqiData())
        );
    }

    private function buildAqiData()
    {
        // ->toArray(): caching ~19k full Eloquent model objects via the file driver's
        // native PHP serialize() (much heavier than the json_encode() this used before
        // caching existed) was exhausting memory. Plain arrays serialize far more cheaply.
        $results = Station::query()->get(self::STATION_FIELDS)->toArray();

        // IQAir readings (e.g. Cambodia/Phnom Penh) cover places WAQI currently has no
        // active stations for at all — merge them in so they actually appear on the map,
        // not just in the hero card's own hardcoded lookup.
        $iqairStations = IqairReading::query()
            ->get(['city', 'state', 'country', 'lat', 'lon', 'aqi', 'pm25', 'pm10', 'pm_estimated', 'temp_c', 'humidity_percent', 'wind_ms'])
            ->map(function ($r) {
                $country = $r->country ?? '';
                $code = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $country), 0, 2)) ?: 'xx';

                return [
                    'name' => trim(implode(', ', array_filter([$r->city, $r->state, $r->country]))),
                    'lat' => $r->lat,
                    'lon' => $r->lon,
                    'aqi' => $r->aqi !== null ? (string) $r->aqi : 'N/A',
                    // Estimated from AQI (EPA formula), not a real sensor reading — see pm_estimated.
                    'pm25' => $r->pm25,
                    'pm10' => $r->pm10,
                    'pm_estimated' => (bool) $r->pm_estimated,
                    'no2' => null,
                    'co' => null,
                    'o3' => null,
                    'so2' => null,
                    'wind_speed' => $r->wind_ms,
                    'wind_dir' => null,
                    'temperature' => $r->temp_c,
                    'humidity' => $r->humidity_percent,
                    'pressure' => null,
                    'flag' => "https://flagcdn.com/w160/{$code}.png",
                ];
            });

        $merged = collect($results)->concat($iqairStations)->values();

        return [
            'status' => 'ok',
            'count'  => $merged->count(),
            'data'   => $merged->toArray(),
        ];
    }

    /**
     * Lightweight list for the compare-cities dropdown (name + flag only).
     * ~1.5MB vs the 5.5MB full `/api/aqi` payload, so the picker fills instantly.
     */
    public function getCities()
    {
        return response()->json(
            Cache::remember('pollution.cities', self::CACHE_TTL_SECONDS, fn () => $this->buildCities())
        );
    }

    private function buildCities()
    {
        $stations = Station::query()->get(['name', 'aqi', 'flag']);

        $iqairStations = IqairReading::query()
            ->get(['city', 'state', 'country', 'aqi'])
            ->map(function ($r) {
                $country = $r->country ?? '';
                $code = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $country), 0, 2)) ?: 'xx';

                return [
                    'name' => trim(implode(', ', array_filter([$r->city, $r->state, $r->country]))),
                    'aqi'  => $r->aqi !== null ? (string) $r->aqi : null,
                    'flag' => "https://flagcdn.com/w160/{$code}.png",
                ];
            });

        $merged = collect($stations)->concat($iqairStations)->values();

        return [
            'status' => 'ok',
            'count'  => $merged->count(),
            'data'   => $merged->toArray(),
        ];
    }

    public function getAqiByCountry()
    {
        return response()->json(
            Cache::remember('pollution.aqi_by_country', self::CACHE_TTL_SECONDS, fn () => $this->buildAqiByCountry())
        );
    }

    private function buildAqiByCountry()
    {
        $stations = Station::query()->get(['name', 'aqi']);

        $byCountry = [];
        foreach ($stations as $station) {
            if (!is_numeric($station->aqi)) continue;

            $parts = explode(',', $station->name);
            $country = trim(end($parts));
            if ($country === '') continue;

            $key = mb_strtolower($country);
            if (!isset($byCountry[$key])) {
                $byCountry[$key] = ['country' => $country, 'total' => 0, 'count' => 0];
            }
            $byCountry[$key]['total'] += (float) $station->aqi;
            $byCountry[$key]['count']++;
        }

        // Include IQAir readings too — e.g. Cambodia has no active WAQI stations at all,
        // so without this it always shows as "No data" on the choropleth regardless of
        // how many IQAir readings we actually have for it.
        $iqairReadings = IqairReading::query()->whereNotNull('aqi')->get(['country', 'aqi']);
        foreach ($iqairReadings as $reading) {
            $country = trim($reading->country ?? '');
            if ($country === '') continue;

            $key = mb_strtolower($country);
            if (!isset($byCountry[$key])) {
                $byCountry[$key] = ['country' => $country, 'total' => 0, 'count' => 0];
            }
            $byCountry[$key]['total'] += (float) $reading->aqi;
            $byCountry[$key]['count']++;
        }

        $data = collect($byCountry)->values()->map(fn ($c) => [
            'country'       => $c['country'],
            'aqi'           => round($c['total'] / $c['count']),
            'station_count' => $c['count'],
        ]);

        return [
            'status' => 'ok',
            'count'  => $data->count(),
            'data'   => $data->toArray(),
        ];
    }
}
