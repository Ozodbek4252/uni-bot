<?php

namespace App\Services\Bots;

use App\Services\BotServiceInterface;
use Illuminate\Support\Facades\Http;

class JomMarbleBotService implements BotServiceInterface
{
    protected $botToken;
    protected $chatId;

    public function __construct($botToken, $chatId)
    {
        $this->botToken = $botToken;
        $this->chatId = $chatId;
    }

    public function sendMessage(array $data)
    {
        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;
        $message = $data['message'] ?? null;
        $theme = $data['theme'] ?? null;
        $time = now()->format('d.m.Y - H:i');

        $sendMessage = "📞 Телефон: {$phone}\n";
        if ($name) {
            $sendMessage .= "👨‍💻 Имя: {$name}\n";
        }
        if ($message) {
            $sendMessage .= "📝 Сообщение: {$message}\n";
        }
        if ($theme) {
            $sendMessage .= "📦 Тема: {$theme}\n";
        }
        $sendMessage .= "⏰ Время заказа: {$time}";

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        Http::post($url, [
            'chat_id' => $this->chatId,
            'text' => $sendMessage,
        ]);
    }
}
