<?php

namespace App\Support;

/**
 * Computes a US EPA AQI sub-index from a raw pollutant concentration using EPA's
 * official piecewise-linear breakpoint tables. Used when a data source (OpenAQ)
 * returns real concentrations instead of a ready-made aggregate AQI.
 *
 * Breakpoints and units follow the US EPA AQI Technical Assistance Document:
 *   PM2.5  → µg/m³
 *   PM10   → µg/m³
 *   O3     → ppm  (8-hour; 1-hour table for concentrations > 0.200 ppm)
 *   CO     → ppm  (8-hour)
 *   SO2    → ppb  (1-hour)
 *   NO2    → ppb  (1-hour)
 */
class AqiCalculator
{
    // Each table: [aqi_low, aqi_high, conc_low, conc_high].
    private const BREAKPOINTS = [
        'pm25' => [
            [0, 50, 0.0, 12.0],
            [51, 100, 12.1, 35.4],
            [101, 150, 35.5, 55.4],
            [151, 200, 55.5, 150.4],
            [201, 300, 150.5, 250.4],
            [301, 400, 250.5, 350.4],
            [401, 500, 350.5, 500.4],
        ],
        'pm10' => [
            [0, 50, 0, 54],
            [51, 100, 55, 154],
            [101, 150, 155, 254],
            [151, 200, 255, 354],
            [201, 300, 355, 424],
            [301, 400, 425, 504],
            [401, 500, 505, 604],
        ],
        'o3' => [
            [0, 50, 0.000, 0.054],
            [51, 100, 0.055, 0.070],
            [101, 150, 0.071, 0.085],
            [151, 200, 0.086, 0.105],
            [201, 300, 0.106, 0.200],
        ],
        'o3_1hr' => [
            [301, 400, 0.405, 0.504],
            [401, 500, 0.505, 0.604],
        ],
        'co' => [
            [0, 50, 0.0, 4.4],
            [51, 100, 4.5, 9.4],
            [101, 150, 9.5, 12.4],
            [151, 200, 12.5, 15.4],
            [201, 300, 15.5, 30.4],
            [301, 400, 30.5, 40.4],
            [401, 500, 40.5, 50.4],
        ],
        'so2' => [
            [0, 50, 0, 35],
            [51, 100, 36, 75],
            [101, 150, 76, 185],
            [151, 200, 186, 304],
            [201, 300, 305, 604],
            [301, 400, 605, 804],
            [401, 500, 805, 1004],
        ],
        'no2' => [
            [0, 50, 0, 53],
            [51, 100, 54, 100],
            [101, 150, 101, 360],
            [151, 200, 361, 649],
            [201, 300, 650, 1249],
            [301, 400, 1250, 1649],
            [401, 500, 1650, 2049],
        ],
    ];

    /**
     * Returns the AQI sub-index (0-500) for one pollutant, or null when the
     * concentration is missing, negative, or outside the standard range.
     */
    public static function subIndex(string $pollutant, ?float $concentration): ?int
    {
        if ($concentration === null || $concentration < 0) {
            return null;
        }

        if ($pollutant === 'o3' && $concentration > 0.200) {
            return self::indexFor($concentration, self::BREAKPOINTS['o3_1hr']);
        }

        $table = self::BREAKPOINTS[$pollutant] ?? null;
        if ($table === null) {
            return null;
        }

        return self::indexFor($concentration, $table);
    }

    private static function indexFor(float $c, array $breakpoints): ?int
    {
        foreach ($breakpoints as [$aqiLow, $aqiHigh, $concLow, $concHigh]) {
            if ($c >= $concLow && $c <= $concHigh) {
                $aqi = (($aqiHigh - $aqiLow) / ($concHigh - $concLow)) * ($c - $concLow) + $aqiLow;
                return (int) round($aqi);
            }
        }

        // Above the highest breakpoint the standard saturates at 500.
        $last = $breakpoints[count($breakpoints) - 1];
        if ($c > $last[3]) {
            return 500;
        }

        return null; // below the lowest breakpoint
    }
}
