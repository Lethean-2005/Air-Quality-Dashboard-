<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AqiHistory;
use App\Models\IqairReading;
use App\Support\AqiPollutantEstimator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AirQualityController extends Controller
{
    /**
     * IQAir icon codes follow the same "NNx" convention (two digits + d/n) as
     * OpenWeather's, but IQAir's nearest_city endpoint doesn't return a text
     * description — derive a short one from the icon code group.
     */
    private const ICON_DESCRIPTIONS = [
        '01' => 'Clear',
        '02' => 'Partly Cloudy',
        '03' => 'Cloudy',
        '04' => 'Overcast',
        '09' => 'Light Rain',
        '10' => 'Rain',
        '11' => 'Thunderstorm',
        '13' => 'Snow',
        '50' => 'Fog',
    ];

    // IQAir's free tier has a tight monthly/per-minute quota, and the frontend polls this
    // endpoint every 30s — a stored reading kept fresh within this window keeps that well
    // within quota. IQAir's own station data typically only updates roughly hourly anyway.
    private const FRESHNESS_SECONDS = 600;

    public function getPhnomPenhAirQualityOpenWeather(Request $request)
    {
        // Round to ~1.1km precision so nearby requests (GPS jitter) share the same row
        // instead of each one creating a brand new row.
        $lat = round((float) $request->query('lat', 11.562108), 2);
        $lon = round((float) $request->query('lon', 104.888535), 2);

        $reading = IqairReading::where('lat', $lat)->where('lon', $lon)->first();

        $isFresh = $reading && $reading->fetched_at->gt(now()->subSeconds(self::FRESHNESS_SECONDS));

        if (!$isFresh) {
            $fresh = $this->fetchFromIqair($lat, $lon);

            if ($fresh !== null) {
                $reading = IqairReading::updateOrCreate(
                    ['lat' => $lat, 'lon' => $lon],
                    $fresh + ['fetched_at' => now()]
                );

                if ($reading->aqi !== null) {
                    $name = trim(implode(', ', array_filter([$reading->city, $reading->state, $reading->country])));
                    if ($name !== '') {
                        AqiHistory::create([
                            'name' => $name,
                            'lat' => $lat,
                            'lon' => $lon,
                            'aqi' => $reading->aqi,
                            'source' => 'iqair',
                            'recorded_at' => now(),
                        ]);
                    }
                }
            } elseif (!$reading) {
                // No stored reading to fall back to, and the live fetch just failed.
                return response()->json(['error' => 'Failed to fetch data from IQAir'], 500);
            }
            // else: live fetch failed but we have a (stale) stored reading — serve that
            // rather than error out, so a transient IQAir blip doesn't blank the panel.
        }

        return response()->json($this->toResponsePayload($reading));
    }

    /**
     * Fetches from IQAir and returns fields matching the iqair_readings table's columns
     * (not the frontend's response shape — that mapping happens in toResponsePayload()).
     */
    private function fetchFromIqair(float $lat, float $lon): ?array
    {
        $apiKey = env('IQAIR_API_KEY');
        $url = "https://api.airvisual.com/v2/nearest_city?lat={$lat}&lon={$lon}&key={$apiKey}";

        try {
            $response = Http::get($url);

            if ($response->failed() || $response->json('status') !== 'success') {
                return null;
            }

            $data = $response->json('data') ?? [];
            $current = $data['current'] ?? [];
            $pollution = $current['pollution'] ?? [];
            $weather = $current['weather'] ?? [];

            $aqi = $pollution['aqius'] ?? null; // IQAir's US AQI is already on the standard 0-500 scale
            $icon = $weather['ic'] ?? null;
            $iconGroup = $icon ? substr($icon, 0, 2) : null;

            // IQAir's nearest_city endpoint doesn't give real PM2.5/PM10 concentrations —
            // estimate whichever one is the dominant pollutant from the AQI value instead.
            $pmEstimate = AqiPollutantEstimator::estimate($aqi, $pollution['mainus'] ?? null);

            return [
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'aqi' => $aqi,
                'status' => $this->aqiStatusLabel($aqi),
                'pm25' => $pmEstimate['pm25'],
                'pm10' => $pmEstimate['pm10'],
                'pm_estimated' => $pmEstimate['estimated'],
                'temp_c' => $weather['tp'] ?? null,
                'humidity_percent' => $weather['hu'] ?? null,
                'pressure_hpa' => $weather['pr'] ?? null,
                'wind_ms' => $weather['ws'] ?? null,
                'weather_description' => $iconGroup ? (self::ICON_DESCRIPTIONS[$iconGroup] ?? null) : null,
                'weather_icon' => $icon,
                'uv_index' => null, // not available on nearest_city
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function toResponsePayload(IqairReading $reading): array
    {
        return [
            'AQI' => $reading->aqi,
            'Status' => $reading->status,
            // PM2_5/PM10: whichever one IQAir reported as the dominant pollutant is an
            // EPA-formula estimate from the AQI (see PM_Estimated); the other is genuinely
            // unavailable. NO2/CO/O3 have no IQAir equivalent at all on this tier.
            'PM2_5' => $reading->pm25,
            'PM10' => $reading->pm10,
            'PM_Estimated' => (bool) $reading->pm_estimated,
            'NO2' => null,
            'CO' => null,
            'O3' => null,
            'Temp_C' => $reading->temp_c,
            'Humidity_percent' => $reading->humidity_percent,
            'Pressure_hPa' => $reading->pressure_hpa,
            'Wind_m_s' => $reading->wind_ms,
            'Weather_Description' => $reading->weather_description,
            'Weather_Icon' => $reading->weather_icon,
            'UV_Index' => $reading->uv_index,
        ];
    }

    /**
     * Standard 0-500 US AQI category thresholds — matches the frontend's own scale
     * (heroAqiLevels in HomePage.vue) so the label is always consistent with the color/badge.
     */
    private function aqiStatusLabel(?int $aqi): string
    {
        if ($aqi === null) return 'Unknown';
        if ($aqi <= 50) return 'Good';
        if ($aqi <= 100) return 'Moderate';
        if ($aqi <= 150) return 'Poor';
        if ($aqi <= 200) return 'Unhealthy';
        if ($aqi <= 300) return 'Severe';
        return 'Hazardous';
    }
}
