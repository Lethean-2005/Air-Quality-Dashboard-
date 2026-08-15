<?php

namespace App\Support;

/**
 * Estimates a pollutant concentration (µg/m³) from a US AQI value using EPA's official
 * piecewise-linear breakpoint formula — the same formula used to compute AQI from a real
 * concentration in the first place, just applied in reverse. This is only an estimate: the
 * true concentration could be anywhere within the AQI's breakpoint band. Used when a data
 * source (IQAir's free-tier endpoints) gives an aggregate AQI + dominant pollutant, but not
 * the actual measured concentration.
 */
class AqiPollutantEstimator
{
    // [aqi_low, aqi_high, conc_low, conc_high] per breakpoint band.
    private const PM25_BREAKPOINTS = [
        [0, 50, 0.0, 12.0],
        [51, 100, 12.1, 35.4],
        [101, 150, 35.5, 55.4],
        [151, 200, 55.5, 150.4],
        [201, 300, 150.5, 250.4],
        [301, 400, 250.5, 350.4],
        [401, 500, 350.5, 500.4],
    ];

    private const PM10_BREAKPOINTS = [
        [0, 50, 0, 54],
        [51, 100, 55, 154],
        [101, 150, 155, 254],
        [151, 200, 255, 354],
        [201, 300, 355, 424],
        [301, 400, 425, 504],
        [401, 500, 505, 604],
    ];

    /**
     * Given an AQI value and IQAir's "main pollutant" code (mainus: p2=PM2.5, p1=PM10,
     * o3/n2/s2/co = others we don't estimate), returns ['pm25' => ?float, 'pm10' => ?float,
     * 'estimated' => bool] — only the field matching the dominant pollutant is filled in,
     * since we have no basis to estimate the other one.
     */
    public static function estimate(?int $aqi, ?string $dominantPollutantCode): array
    {
        $result = ['pm25' => null, 'pm10' => null, 'estimated' => false];

        if ($aqi === null || $dominantPollutantCode === null) {
            return $result;
        }

        if ($dominantPollutantCode === 'p2') {
            $result['pm25'] = self::concentrationFor($aqi, self::PM25_BREAKPOINTS);
            $result['estimated'] = $result['pm25'] !== null;
        } elseif ($dominantPollutantCode === 'p1') {
            $result['pm10'] = self::concentrationFor($aqi, self::PM10_BREAKPOINTS);
            $result['estimated'] = $result['pm10'] !== null;
        }

        return $result;
    }

    private static function concentrationFor(int $aqi, array $breakpoints): ?float
    {
        foreach ($breakpoints as [$aqiLow, $aqiHigh, $concLow, $concHigh]) {
            if ($aqi >= $aqiLow && $aqi <= $aqiHigh) {
                $concentration = (($concHigh - $concLow) / ($aqiHigh - $aqiLow)) * ($aqi - $aqiLow) + $concLow;
                return round($concentration, 1);
            }
        }

        return null; // AQI out of the standard 0-500 range
    }
}
