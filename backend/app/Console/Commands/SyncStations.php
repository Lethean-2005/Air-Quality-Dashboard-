<?php

namespace App\Console\Commands;

use App\Models\AqiHistory;
use App\Models\Station;
use App\Support\AqiCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncStations extends Command
{
    protected $signature = 'stations:sync';
    protected $description = 'Fetch global OpenAQ station data and replace the local stations table with it';

    // OpenAQ v3 parameter IDs chosen for the unit each one reports in, so they match the
    // EPA breakpoint tables used by AqiCalculator (PM in µg/m³, O3/CO in ppm, SO2/NO2 in ppb).
    private const PARAMS = [
        'pm25' => 2,   // µg/m³
        'pm10' => 1,   // µg/m³
        'o3'   => 10,  // ppm
        'co'   => 8,   // ppm
        'so2'  => 101, // ppb
        'no2'  => 15,  // ppb
    ];

    // Pollutant fields stored on a station row: the DB column to write, the unit conversion
    // to apply from the raw API value (ppm -> ppb for CO/O3; the rest are already ppb/µg/m³
    // as displayed), and a sanity cap in the *stored* unit — bogus sensor reads are dropped
    // rather than written into the double(8,2) columns.
    //
    // Each cap sits ~25-50% above AqiCalculator's own breakpoint ceiling for that pollutant
    // (pm25: 500.4, pm10: 604, o3_1hr: 604 ppb, co: 50.4 ppm, so2: 1004, no2: 2049) — wide
    // enough to admit a genuinely extreme reading, tight enough that noisy sensor garbage
    // (e.g. a low-cost PM sensor spiking into the thousands) gets rejected outright instead
    // of sailing past AqiCalculator's own saturation-at-500 branch and faking a "hazardous"
    // station. The old caps (flat 5000/50000) were 5-10x too loose for this to ever trigger.
    private const STORE_CONVERSIONS = [
        'pm25' => ['col' => 'pm25', 'multiplier' => 1,     'max' => 750],
        'pm10' => ['col' => 'pm10', 'multiplier' => 1,     'max' => 900],
        'o3'   => ['col' => 'o3',   'multiplier' => 1000,  'max' => 800],    // <= 0.8 ppm
        'co'   => ['col' => 'co',   'multiplier' => 1000,  'max' => 60000],  // <= 60 ppm
        'so2'  => ['col' => 'so2',  'multiplier' => 1,     'max' => 1500],
        'no2'  => ['col' => 'no2',  'multiplier' => 1,     'max' => 3000],
    ];

    private const PAGE_SIZE = 1000;
    private const MAX_PAGES = 200; // safety bound per resource (~200k records max)

    public function handle(): int
    {
        ini_set('max_execution_time', 1800);

        $apiKey = config('services.openaq.api_key');
        if (!$apiKey) {
            $this->error('OPENAQ_API_KEY is not set in config/services.php or .env');
            return self::FAILURE;
        }

        $headers = ['X-API-Key' => $apiKey];

        $this->info('Fetching OpenAQ locations...');
        $locations = $this->fetchAllPages('/v3/locations', $headers);
        $this->info('  Loaded ' . count($locations) . ' locations.');

        $locationMap = [];
        foreach ($locations as $loc) {
            if (isset($loc['id'])) {
                $locationMap[$loc['id']] = $loc;
            }
        }

        $latest = [];
        foreach (self::PARAMS as $key => $paramId) {
            $this->info("Fetching latest {$key} (parameter {$paramId})...");
            $rows = $this->fetchAllPages("/v3/parameters/{$paramId}/latest", $headers);
            $this->info('  Got ' . count($rows) . ' readings.');

            foreach ($rows as $row) {
                $locId = $row['locationsId'] ?? null;
                if ($locId === null || $row['value'] === null || !is_numeric($row['value'])) {
                    continue;
                }
                // A location can host several sensors for the same pollutant — keep the
                // worst case (highest concentration) so the map errs on the safe side.
                $val = (float) $row['value'];
                if (!isset($latest[$locId][$key]) || $val > $latest[$locId][$key]) {
                    $latest[$locId][$key] = $val;
                }
            }
        }

        $this->info('Building station rows...');
        $rows = [];

        foreach ($latest as $locId => $vals) {
            $loc = $locationMap[$locId] ?? null;
            $coords = $loc['coordinates'] ?? [];

            $lat = $coords['latitude'] ?? null;
            $lon = $coords['longitude'] ?? null;
            if ($lat === null || $lon === null) {
                continue;
            }

            $country = $loc['country'] ?? null;
            $countryCode = strtolower(preg_replace('/[^a-zA-Z]/', '', $country['code'] ?? '')) ?: 'xx';
            $countryName = $country['name'] ?? '';

            $locName = trim($loc['name'] ?? '');
            $name = $countryName !== ''
                ? $locName . ', ' . $countryName
                : $locName;
            if ($name === '') {
                $name = 'Station ' . $locId;
            }

            // Compute each pollutant's sub-index; the station AQI is the dominant one.
            $aqi = null;
            $row = [
                'uid'        => $locId,
                'name'       => $name,
                'lat'        => $lat,
                'lon'        => $lon,
                'aqi'        => 'N/A',
                'pm25'       => null,
                'pm10'       => null,
                'no2'        => null,
                'co'         => null,
                'o3'         => null,
                'so2'        => null,
                'wind_speed' => null,
                'wind_dir'   => null,
                'temperature'=> null,
                'humidity'   => null,
                'pressure'   => null,
                'flag'       => "https://flagcdn.com/w160/{$countryCode}.png",
                'created_at' => now(),
                'updated_at' => now(),
            ];

            foreach ($vals as $pollutant => $value) {
                $conv = self::STORE_CONVERSIONS[$pollutant] ?? null;
                if ($conv === null) {
                    continue;
                }

                $stored = $value * $conv['multiplier'];
                // Some low-cost sensors report absurd or negative sentinel values (e.g.
                // ±9999, even INF); drop those so a single bad reading can't blow up the
                // column range or skew the AQI.
                if (!is_finite($stored) || $stored < 0 || $stored > $conv['max']) {
                    continue;
                }
                $row[$conv['col']] = $stored;

                $subIndex = AqiCalculator::subIndex($pollutant, $value);
                if ($subIndex !== null && ($aqi === null || $subIndex > $aqi)) {
                    $aqi = $subIndex;
                }
            }

            if ($aqi !== null) {
                $row['aqi'] = (string) $aqi;
            }

            $rows[] = $row;
        }

        if (empty($rows)) {
            $this->error('No station data fetched — leaving the existing table untouched.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($rows) {
            // Not truncate(): TRUNCATE causes an implicit commit in MySQL, which breaks
            // being inside a transaction. A regular delete-all stays fully transactional.
            Station::query()->delete();
            foreach (array_chunk($rows, 500) as $chunk) {
                Station::insert($chunk);
            }
        });

        $this->info('Synced ' . count($rows) . ' stations into the database.');

        // Each sync tick is a real, timestamped AQI reading — append it to the history log
        // (not overwritten like `stations`) so the hero card's "AQI Graph" can chart genuine
        // recent trend. Runs every 30 min via the scheduler, so this builds up naturally.
        $now = now();
        $historyRows = collect($rows)
            ->filter(fn ($r) => is_numeric($r['aqi']))
            ->map(fn ($r) => [
                'name' => $r['name'],
                'lat' => $r['lat'],
                'lon' => $r['lon'],
                'aqi' => (int) $r['aqi'],
                'pm25' => $r['pm25'],
                'pm10' => $r['pm10'],
                'co' => $r['co'],
                'so2' => $r['so2'],
                'no2' => $r['no2'],
                'o3' => $r['o3'],
                'source' => 'openaq',
                'recorded_at' => $now,
            ])->all();

        foreach (array_chunk($historyRows, 500) as $chunk) {
            AqiHistory::insert($chunk);
        }

        $this->info('Appended ' . count($historyRows) . ' readings to AQI history.');

        // Bound the table's growth — 3 days is plenty for a 24-72h trend chart.
        $pruned = AqiHistory::where('recorded_at', '<', $now->copy()->subDays(3))->delete();
        $this->info('Pruned ' . $pruned . ' stale history rows.');

        return self::SUCCESS;
    }

    /**
     * Fetches every page of an OpenAQ v3 resource (limit=1000/page) until a short page.
     */
    private function fetchAllPages(string $path, array $headers): array
    {
        $out = [];

        for ($page = 1; $page <= self::MAX_PAGES; $page++) {
            $sep = str_contains($path, '?') ? '&' : '?';
            $url = "https://api.openaq.org{$path}{$sep}limit=" . self::PAGE_SIZE . "&page={$page}";

            try {
                $response = Http::withHeaders($headers)->timeout(60)->get($url);
            } catch (\Throwable $e) {
                $this->warn("  page {$page} failed (" . $e->getMessage() . ') — stopping.');
                break;
            }

            if ($response->failed()) {
                $this->warn('  page ' . $page . ' failed (HTTP ' . $response->status() . ') — stopping.');
                break;
            }

            $rows = $response->json('results') ?? [];
            $out = array_merge($out, $rows);

            if (count($rows) < self::PAGE_SIZE) {
                break;
            }
        }

        return $out;
    }
}
