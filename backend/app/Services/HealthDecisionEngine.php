<?php

namespace App\Services;

class HealthDecisionEngine
{
    /**
     * AQI category -> the message-template key HealthAlert.vue already uses
     * (messages.good/moderate/unhealthySensitive/unhealthy/veryUnhealthy/hazardous),
     * so the Smart Message can pull the admin's own configured wording.
     */
    private const AQI_STATUS_TO_TEMPLATE_KEY = [
        'Good' => 'good',
        'Moderate' => 'moderate',
        'Poor' => 'unhealthySensitive',
        'Unhealthy' => 'unhealthy',
        'Severe' => 'veryUnhealthy',
        'Hazardous' => 'hazardous',
    ];

    private const AQI_SEVERITY = [
        'Good' => 0,
        'Moderate' => 1,
        'Poor' => 2,
        'Unhealthy' => 3,
        'Severe' => 4,
        'Hazardous' => 5,
        'Unknown' => -1,
    ];

    /**
     * Mirrors HealthAlert.vue's `defaultMessages.*.public` wording, so the Telegram bot can
     * compose a message on its own (e.g. replying to /start) without a browser session —
     * the admin's edited templates only live in that page's localStorage, never here.
     */
    private const DEFAULT_PUBLIC_MESSAGES = [
        'good' => 'Air quality is good. Enjoy your outdoor activities!',
        'moderate' => 'Air quality is moderate. Sensitive groups should take care.',
        'unhealthySensitive' => 'Unhealthy for sensitive groups. Limit prolonged outdoor exertion.',
        'unhealthy' => 'Unhealthy air quality. Everyone should reduce outdoor activities.',
        'veryUnhealthy' => 'Very unhealthy air quality. Health alert for everyone.',
        'hazardous' => 'Hazardous air quality. Health emergency for everyone.',
    ];

    public static function defaultPublicMessage(string $templateKey): string
    {
        return self::DEFAULT_PUBLIC_MESSAGES[$templateKey] ?? self::DEFAULT_PUBLIC_MESSAGES['good'];
    }

    /**
     * Combines an AQI reading with current weather into a single recommendation.
     * $aqi:     ['aqi' => int|null, 'status' => string]
     * $weather: ['temp' => float|null, 'humidity' => int|null, 'wind_speed' => float|null, 'rain_chance' => int|null]
     */
    public static function decide(array $aqi, array $weather): array
    {
        $status = $aqi['status'] ?? 'Unknown';
        $templateKey = self::AQI_STATUS_TO_TEMPLATE_KEY[$status] ?? 'good';
        $severity = self::AQI_SEVERITY[$status] ?? 0;

        $temp = $weather['temp'] ?? null;
        $wind = $weather['wind_speed'] ?? null;
        $rainChance = $weather['rain_chance'] ?? null;
        $humidity = $weather['humidity'] ?? null;

        $weatherNotes = [];
        if ($temp !== null && $temp >= 34) $weatherNotes[] = 'very hot';
        if ($humidity !== null && $humidity >= 80) $weatherNotes[] = 'high humidity';
        if ($rainChance !== null && $rainChance >= 60) $weatherNotes[] = 'rain likely';
        if ($wind !== null && $wind >= 10) $weatherNotes[] = 'strong wind';

        $weatherSummary = $temp !== null
            ? sprintf('%d°C%s', round($temp), $weatherNotes ? ', ' . implode(', ', $weatherNotes) : '')
            : 'Weather data unavailable';

        $activityRecommendation = self::activityRecommendation($severity, $weatherNotes, $rainChance);

        return [
            'aqi_level' => $status,
            'aqi_template_key' => $templateKey,
            'aqi_severity' => $severity,
            'weather_summary' => $weatherSummary,
            'weather_flags' => $weatherNotes,
            'activity_recommendation' => $activityRecommendation,
        ];
    }

    private static function activityRecommendation(int $severity, array $weatherNotes, ?int $rainChance): string
    {
        if ($severity >= 4) {
            return 'Avoid outdoor activities. Stay indoors with windows closed and, if possible, run an air purifier.';
        }

        if ($severity === 3) {
            return 'Limit outdoor activity to short, low-effort trips. Sensitive groups should stay indoors.';
        }

        if ($severity === 2) {
            $note = in_array('rain likely', $weatherNotes, true) ? ' Bring a rain jacket if you head out.' : '';
            return 'Air quality is a concern for sensitive groups — limit prolonged or intense outdoor exertion.' . $note;
        }

        // Good/Moderate — outdoor activity is fine, weather is now the deciding factor.
        if (in_array('rain likely', $weatherNotes, true)) {
            return 'Air quality is fine for outdoor activities, but rain is likely — bring a rain jacket or plan indoors.';
        }
        if (in_array('very hot', $weatherNotes, true)) {
            return 'Air quality is fine, but it is very hot — stay hydrated and avoid peak midday sun.';
        }
        if (in_array('strong wind', $weatherNotes, true)) {
            return 'Air quality is fine, though winds are strong — secure loose items if you are outdoors.';
        }

        return 'Great conditions for outdoor activities today.';
    }
}
