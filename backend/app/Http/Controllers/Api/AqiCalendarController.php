<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AqiHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AqiCalendarController extends Controller
{
    // Same metric whitelist as AqiHistoryController.
    private const METRICS = ['aqi', 'pm25', 'pm10', 'co', 'so2', 'no2', 'o3'];

    /**
     * One real average-per-day value per date in the given year, from the same aqi_history
     * table the AQI Graph reads — no fake/backfilled data, so days before this station's
     * first sync (or the whole aqi_history table's creation) simply have no entry.
     */
    public function index(Request $request)
    {
        $name = trim((string) $request->query('name', ''));
        $year = (int) $request->query('year', now()->year);
        $metric = strtolower(trim((string) $request->query('metric', 'aqi')));

        if ($name === '') {
            return response()->json(['status' => 'error', 'message' => 'name is required'], 422);
        }

        if (!in_array($metric, self::METRICS, true)) {
            return response()->json(['status' => 'error', 'message' => 'invalid metric'], 422);
        }

        $nameQuery = AqiHistory::query();
        $hasExact = (clone $nameQuery)->where('name', $name)->exists();

        if (!$hasExact) {
            $parts = array_map('trim', explode(',', $name));
            $city = $parts[0] ?? '';
            $country = end($parts);

            if ($city !== '' && $city !== $name) {
                $nameQuery->where('name', 'like', $city . ',%');
                if ($country !== '' && $country !== $city) {
                    $nameQuery->where('name', 'like', '%' . $country);
                }
            } else {
                $nameQuery->where('name', $name);
            }
        } else {
            $nameQuery->where('name', $name);
        }

        $rows = (clone $nameQuery)
            ->whereYear('recorded_at', $year)
            ->whereNotNull($metric)
            ->select(DB::raw('DATE(recorded_at) as day'), DB::raw("AVG($metric) as value"))
            ->groupBy('day')
            ->get();

        $days = [];
        foreach ($rows as $r) {
            $days[$r->day] = round((float) $r->value, 1);
        }

        $availableYears = (clone $nameQuery)
            ->whereNotNull($metric)
            ->select(DB::raw('DISTINCT YEAR(recorded_at) as y'))
            ->orderBy('y')
            ->pluck('y')
            ->map(fn ($y) => (int) $y)
            ->values();

        return response()->json([
            'status' => 'ok',
            'name' => $name,
            'year' => $year,
            'metric' => $metric,
            'days' => $days,
            'available_years' => $availableYears,
        ]);
    }
}
