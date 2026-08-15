<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HealthDecisionEngine;
use App\Services\TelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HealthAlertDecisionController extends Controller
{
    /**
     * Location -> AQI Data + Weather -> Decision Engine -> Smart Message pipeline.
     * Reuses the existing AQI (IQAir) and weather (OpenWeather) endpoints in-process
     * rather than duplicating their fetch/cache logic.
     */
    public function decide(Request $request, AirQualityController $airQualityController, WeatherForecastController $weatherController)
    {
        $lat = round((float) $request->query('lat', 11.562108), 2);
        $lon = round((float) $request->query('lon', 104.888535), 2);

        $aqiResponse = $airQualityController->getPhnomPenhAirQualityOpenWeather($request);
        $aqiPayload = $aqiResponse->getData(true);

        $weatherResponse = $weatherController->index($request);
        $weatherPayload = $weatherResponse->getData(true);
        $weatherOk = ($weatherPayload['status'] ?? null) === 'ok';

        $aqiForEngine = [
            'aqi' => $aqiPayload['AQI'] ?? null,
            'status' => $aqiPayload['Status'] ?? 'Unknown',
        ];

        $weatherForEngine = [
            'temp' => $weatherOk ? $weatherPayload['current']['temp'] : ($aqiPayload['Temp_C'] ?? null),
            'humidity' => $weatherOk ? $weatherPayload['current']['humidity'] : ($aqiPayload['Humidity_percent'] ?? null),
            'wind_speed' => $weatherOk ? $weatherPayload['current']['wind_speed'] : ($aqiPayload['Wind_m_s'] ?? null),
            'rain_chance' => $weatherOk ? $weatherPayload['current']['pop_next'] : null,
        ];

        $decision = HealthDecisionEngine::decide($aqiForEngine, $weatherForEngine);

        return response()->json([
            'status' => 'ok',
            'location' => ['lat' => $lat, 'lon' => $lon],
            'aqi' => [
                'value' => $aqiPayload['AQI'] ?? null,
                'status' => $aqiPayload['Status'] ?? 'Unknown',
                'pm25' => $aqiPayload['PM2_5'] ?? null,
                'pm10' => $aqiPayload['PM10'] ?? null,
            ],
            'weather' => [
                'temp' => $weatherForEngine['temp'],
                'humidity' => $weatherForEngine['humidity'],
                'wind_speed' => $weatherForEngine['wind_speed'],
                'rain_chance' => $weatherForEngine['rain_chance'],
                'description' => $weatherOk ? $weatherPayload['current']['description'] : ($aqiPayload['Weather_Description'] ?? null),
                'source' => $weatherOk ? 'forecast' : 'aqi_station',
            ],
            'decision' => $decision,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Sends a given (or freshly-generated) smart message to a Telegram chat.
     * The chat_id is obtained by messaging the bot once, then calling GET
     * /api/health-alert/telegram-updates to read it back.
     */
    public function sendTelegram(Request $request)
    {
        $chatId = (string) $request->input('chat_id', '');
        $message = (string) $request->input('message', '');

        if ($chatId === '' || $message === '') {
            return response()->json(['status' => 'error', 'message' => 'chat_id and message are required'], 422);
        }

        $sent = TelegramNotifier::send($chatId, $message);

        return response()->json([
            'status' => $sent ? 'ok' : 'error',
            'message' => $sent ? 'Sent' : 'Failed to send — check the bot token and chat_id',
        ], $sent ? 200 : 502);
    }

    /**
     * Telegram calls this URL directly (no auth — Telegram isn't a logged-in user) whenever
     * someone messages the bot, once it's registered via the Bot API's setWebhook. Telegram
     * only accepts a public HTTPS URL here, so this has no effect until one is registered:
     *   curl -F "url=https://<your-public-domain>/api/telegram/webhook" \
     *        https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook
     * On localhost, expose the dev server first (e.g. `ngrok http 8001`) and use that URL.
     */
    public function telegramWebhook(Request $request, AirQualityController $airQualityController, WeatherForecastController $weatherController)
    {
        $chatId = $request->input('message.chat.id');
        $text = trim((string) $request->input('message.text', ''));

        // Only /start triggers an auto-reply for now; anything else is acknowledged and ignored.
        if ($chatId === null || strpos($text, '/start') !== 0) {
            return response()->json(['ok' => true]);
        }

        $decisionPayload = $this->decide($request, $airQualityController, $weatherController)->getData(true);
        $decision = $decisionPayload['decision'] ?? [];

        $publicMessage = HealthDecisionEngine::defaultPublicMessage($decision['aqi_template_key'] ?? 'good');
        $activity = $decision['activity_recommendation'] ?? '';
        $text = trim("{$publicMessage} {$activity}");

        TelegramNotifier::send((string) $chatId, $text);

        return response()->json(['ok' => true]);
    }

    /**
     * Reads back recent messages sent TO the bot, so an admin can find their
     * own chat_id after messaging the bot for the first time (no curl needed).
     */
    public function telegramUpdates()
    {
        $token = config('services.telegram.bot_token');
        if (!$token) {
            return response()->json(['status' => 'error', 'message' => 'Telegram bot token not configured'], 500);
        }

        $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getUpdates");

        if ($response->failed()) {
            return response()->json(['status' => 'error', 'message' => 'Failed to reach Telegram'], 502);
        }

        $chats = collect($response->json('result') ?? [])
            ->map(fn ($u) => $u['message']['chat'] ?? null)
            ->filter()
            ->unique('id')
            ->values();

        return response()->json(['status' => 'ok', 'chats' => $chats]);
    }
}
