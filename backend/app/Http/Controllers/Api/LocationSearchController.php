<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IqairReading;
use App\Models\Station;
use Illuminate\Http\Request;

class LocationSearchController extends Controller
{
    /**
     * WAQI station names are inconsistently formatted — many US stations end the name
     * with just the state ("..., Bowie, Texas") instead of "USA", so naively taking the
     * last comma segment as the "country" misreports the state as the country. Recognize
     * these and normalize to the real country instead.
     */
    private const US_STATES = [
        'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut',
        'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
        'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan',
        'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada',
        'New Hampshire', 'New Jersey', 'NewJersey', 'New Mexico', 'New York',
        'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania',
        'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah',
        'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming',
        'District of Columbia',
    ];

    /**
     * Search locations by city name, country name, or full station name.
     * Merges two sources: the `stations` table (global WAQI network, kept fresh by
     * `php artisan stations:sync`) and `iqair_readings` (locations looked up via the
     * IQAir-backed hero panel, e.g. Phnom Penh/Cambodia — which WAQI currently has no
     * active stations for at all, so without this merge those places are unsearchable
     * even though the app already has real data for them).
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        // Minimum 2 characters for search
        if (strlen($query) < 2) {
            return response()->json([
                'status' => 'ok',
                'data' => []
            ]);
        }

        $stationMatches = Station::query()
            ->where('name', 'like', "%{$query}%")
            ->limit(100) // headroom before city/country splitting + de-dupe below
            ->get(['name', 'aqi', 'lat', 'lon'])
            ->map(function ($station) {
                $parts = explode(',', $station->name);
                $city = trim($parts[0]);
                $country = count($parts) > 1 ? trim(end($parts)) : 'Unknown';

                // Clean country name (remove parentheses content)
                $cleanCountry = preg_replace('/\s*\([^)]*\)/', '', $country);

                // A US state name in the "country" position means this station's name
                // omitted "USA" — the real country is the United States.
                if (in_array($cleanCountry, self::US_STATES, true)) {
                    $cleanCountry = 'United States';
                }

                return [
                    'name' => $city,
                    'full_name' => $station->name,
                    'country' => $cleanCountry,
                    'lat' => $station->lat,
                    'lon' => $station->lon,
                    'aqi' => $station->aqi,
                ];
            });

        $iqairMatches = IqairReading::query()
            ->where(function ($q) use ($query) {
                $q->where('city', 'like', "%{$query}%")
                  ->orWhere('country', 'like', "%{$query}%")
                  ->orWhere('state', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get(['city', 'state', 'country', 'lat', 'lon', 'aqi'])
            ->map(fn ($r) => [
                'name' => $r->city ?? 'Unknown',
                'full_name' => trim(implode(', ', array_filter([$r->city, $r->state, $r->country]))),
                'country' => $r->country ?? 'Unknown',
                'lat' => $r->lat,
                'lon' => $r->lon,
                'aqi' => $r->aqi !== null ? (string) $r->aqi : null,
            ]);

        $locations = $stationMatches
            ->concat($iqairMatches)
            ->unique('full_name')
            ->take(30) // limit to 30 results shown
            ->map(function ($location) use ($query) {
                // Determine search match type for better UI display
                $type = 'general';
                if (stripos($location['name'], $query) === 0) {
                    $type = 'city';
                } elseif (stripos($location['country'], $query) === 0) {
                    $type = 'country';
                } elseif (stripos($location['name'], $query) !== false) {
                    $type = 'city_partial';
                } elseif (stripos($location['country'], $query) !== false) {
                    $type = 'country_partial';
                }

                $location['type'] = $type;
                return $location;
            })
            ->values();

        return response()->json([
            'status' => 'ok',
            'data' => $locations,
            'total_available' => Station::count() + IqairReading::count(),
        ]);
    }
}
