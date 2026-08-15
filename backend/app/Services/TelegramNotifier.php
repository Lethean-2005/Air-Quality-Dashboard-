<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramNotifier
{
    /**
     * Sends a text message via the Telegram Bot API.
     * $chatId is the numeric chat id of the user/group that has started a chat
     * with the bot (get it by messaging the bot, then GET .../getUpdates).
     */
    public static function send(string $chatId, string $text): bool
    {
        $token = config('services.telegram.bot_token');
        if (!$token) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            return $response->successful() && ($response->json('ok') === true);
        } catch (\Exception $e) {
            return false;
        }
    }
}
