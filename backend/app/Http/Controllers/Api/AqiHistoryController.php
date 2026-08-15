<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AqiHistory;
use Illuminate\Http\Request;

class AqiHistoryController extends Controller
{
    // Metric -> [DB column, display unit]. PM2.5/PM10 are genuine µg/m³ readings; CO/SO2/NO2/O3
    // are stored as ppb (see SyncStations::STORE_CONVERSIONS).
    private const METRICS = [
        'aqi'  => ['col' => 'aqi',  'unit' => ''],
        'pm25' => ['col' => 'pm25', 'unit' => 'µg/m³'],
        'pm10' => ['col' => 'pm10', 'unit' => 'µg/m³'],
        'co'   => ['col' => 'co',   'unit' => 'ppb'],
        'so2'  => ['col' => 'so2',  'unit' => 'ppb'],
        'no2'  => ['col' => 'no2',  'unit' => 'ppb'],
        'o3'   => ['col' => 'o3',   'unit' => 'ppb'],
    ];

    /**
     * Real recorded pollutant points for a station name, going back `hours` (default 24).
     * Points only exist from whenever this station was first synced/viewed onward — there
     * is no backfilled/fake data, so recently-added stations will show a short history that
     * grows over time as `stations:sync` (every 30 min) and live hero-card fetches record it.
     */
    public function index(Request $request)
    {
        $name = trim((string) $request->query('name', ''));
        $hours = max(1, min(24 * 30, (int) $request->query('hours', 24)));
        $metricKey = strtolower(trim((string) $request->query('metric', 'aqi')));

        if ($name === '') {
            return response()->json(['status' => 'error', 'message' => 'name is required'], 422);
        }

        if (!isset(self::METRICS[$metricKey])) {
            return response()->json(['status' => 'error', 'message' => 'invalid metric'], 422);
        }

        $col = self::METRICS[$metricKey]['col'];
        $unit = self::METRICS[$metricKey]['unit'];

        $base = AqiHistory::query()
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->whereNotNull($col);

        // Exact station-name match first. Station names drift between syncs (e.g. IQAir's
        // "Phnom Penh, Phnom Penh, Cambodia" vs OpenAQ's "Phnom Penh, Cambodia"), so if the
        // exact name has no history yet, fall back to a partial match on city + country.
        $hasExact = (clone $base)->where('name', $name)->exists();

        if (!$hasExact) {
            $parts = array_map('trim', explode(',', $name));
            $city = $parts[0] ?? '';
            $country = end($parts);

            if ($city !== '' && $city !== $name) {
                $base->where('name', 'like', $city . ',%');
                if ($country !== '' && $country !== $city) {
                    $base->where('name', 'like', '%' . $country);
                }
            } else {
                $base->where('name', $name);
            }
        } else {
            $base->where('name', $name);
        }

        $rows = $base->orderBy('recorded_at')->get(['recorded_at', $col]);

        $points = $rows->map(fn ($r) => [
            'time' => $r->recorded_at->toIso8601String(),
            'value' => (float) $r->{$col},
            // Kept for older consumers that still read the AQI-only shape directly.
            'aqi' => $metricKey === 'aqi' ? (int) $r->{$col} : null,
        ])->values();

        $min = $rows->sortBy($col)->first();
        $max = $rows->sortByDesc($col)->first();

        $toPoint = fn ($r) => $r ? [
            'value' => (float) $r->{$col},
            'aqi' => $metricKey === 'aqi' ? (int) $r->{$col} : null,
            'time' => $r->recorded_at->toIso8601String(),
        ] : null;

        return response()->json([
            'status' => 'ok',
            'name' => $name,
            'hours' => $hours,
            'metric' => $metricKey,
            'unit' => $unit,
            'points' => $points,
            'min' => $toPoint($min),
            'max' => $toPoint($max),
        ]);
    }
}
