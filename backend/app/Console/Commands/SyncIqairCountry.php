<?php

namespace App\Console\Commands;

use App\Models\AqiHistory;
use App\Models\IqairReading;
use App\Support\AqiPollutantEstimator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SyncIqairCountry extends Command
{
    protected $signature = 'iqair:sync-country {countries=Cambodia}';
    protected $description = 'Populate iqair_readings with every city IQAir has for one or more countries (comma-separated; walks states -> cities -> per-city AQI)';

    // IQAir's free/Community tier is tightly rate-limited — confirmed via live testing that
    // ~15s between calls is needed to reliably avoid "Too Many Requests" (1.1s was nowhere
    // near enough, it started failing after the very first city-list call).
    private const REQUEST_DELAY_MICROSECONDS = 15_500_000; // 15.5s

    public function handle(): int
    {
        $countries = collect(explode(',', $this->argument('countries')))
            ->map(fn ($c) => trim($c))
            ->filter()
            ->values();

        if ($countries->isEmpty()) {
            $this->error('No countries given.');
            return self::FAILURE;
        }

        $totalSynced = 0;
        $totalFailed = 0;

        foreach ($countries as $country) {
            $this->info("=== {$country} ===");
            $result = $this->syncCountry($country);
            $totalSynced += $result['synced'];
            $totalFailed += $result['failed'];
        }

        $this->info("Done — synced {$totalSynced} cities ({$totalFailed} failed) across {$countries->count()} country(s).");

        Cache::forget('pollution.aqi_data');
        Cache::forget('pollution.cities');
        Cache::forget('pollution.aqi_by_country');

        return self::SUCCESS;
    }

    private function syncCountry(string $country): array
    {
        $apiKey = env('IQAIR_API_KEY');

        $this->info("Fetching states for {$country}...");
        $states = $this->get("https://api.airvisual.com/v2/states?country=" . urlencode($country) . "&key={$apiKey}");

        if ($states === null) {
            $this->error("Could not fetch states for {$country} — skipping.");
            return ['synced' => 0, 'failed' => 0];
        }

        $stateNames = collect($states)->pluck('state');
        $this->info("Found {$stateNames->count()} states/provinces.");

        $synced = 0;
        $failed = 0;

        foreach ($stateNames as $state) {
            usleep(self::REQUEST_DELAY_MICROSECONDS);

            $cities = $this->get(
                "https://api.airvisual.com/v2/cities?state=" . urlencode($state) . "&country=" . urlencode($country) . "&key={$apiKey}"
            );

            if ($cities === null) {
                $this->warn("  Failed to list cities for {$state}");
                continue;
            }

            $cityNames = collect($cities)->pluck('city');
            $this->line("  {$state}: {$cityNames->count()} cities");

            foreach ($cityNames as $city) {
                usleep(self::REQUEST_DELAY_MICROSECONDS);

                $detail = $this->get(
                    "https://api.airvisual.com/v2/city?city=" . urlencode($city)
                    . "&state=" . urlencode($state) . "&country=" . urlencode($country) . "&key={$apiKey}"
                );

                if ($detail === null) {
                    $failed++;
                    continue;
                }

                $this->storeReading($detail);
                $synced++;
            }
        }

        $this->info("  Synced {$synced} cities ({$failed} failed).");
        return ['synced' => $synced, 'failed' => $failed];
    }

    private function storeReading(array $data): void
    {
        $coords = $data['location']['coordinates'] ?? null; // [lon, lat]
        if (!$coords) return;

        $lat = round((float) $coords[1], 2);
        $lon = round((float) $coords[0], 2);

        $current = $data['current'] ?? [];
        $pollution = $current['pollution'] ?? [];
        $weather = $current['weather'] ?? [];
        $aqi = $pollution['aqius'] ?? null;

        // Same EPA-formula estimate as the single-location endpoint — see AqiPollutantEstimator.
        $pmEstimate = AqiPollutantEstimator::estimate($aqi, $pollution['mainus'] ?? null);

        IqairReading::updateOrCreate(
            ['lat' => $lat, 'lon' => $lon],
            [
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
                'weather_description' => null,
                'weather_icon' => $weather['ic'] ?? null,
                'uv_index' => null,
                'fetched_at' => now(),
            ]
        );

        if ($aqi !== null) {
            // Same display name format PollutionDataController builds for merged IQAir
            // entries — must match exactly so the frontend's history lookup by name works.
            $name = trim(implode(', ', array_filter([$data['city'] ?? null, $data['state'] ?? null, $data['country'] ?? null])));
            if ($name !== '') {
                AqiHistory::create([
                    'name' => $name,
                    'lat' => $lat,
                    'lon' => $lon,
                    'aqi' => $aqi,
                    'source' => 'iqair',
                    'recorded_at' => now(),
                ]);
            }
        }
    }

    private function get(string $url, int $attempt = 1): ?array
    {
        try {
            $response = Http::timeout(15)->get($url);

            if ($response->successful() && $response->json('status') === 'success') {
                return $response->json('data');
            }

            // A transient rate-limit hit shouldn't sink a 30-minute run — back off harder
            // each time and retry (up to 3 attempts total) instead of giving up after one.
            $message = $response->json('data.message');
            if ($attempt < 3 && $message === 'Too Many Requests') {
                usleep(self::REQUEST_DELAY_MICROSECONDS * ($attempt + 1));
                return $this->get($url, $attempt + 1);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

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
