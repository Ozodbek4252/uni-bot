<?php

namespace App\Services\Bots;

use App\Services\BotServiceInterface;
use Illuminate\Support\Facades\Http;

class ArbossBotService implements BotServiceInterface
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
        $message = $data['message'] ?? null;
        $phone = $data['phone'] ?? null;
        $file = $data['file'] ?? null;
        $time = now()->timezone('Asia/Tashkent')->format('Y-m-d H:i:s');

        // validation for file if exists
        if ($file) {
            if (!file_exists($file)) {
                return response()->json(['error' => 'File not found'], 404);
            }
        }

        $sendMessage = "🆔 Foydalanuvchi: {$name}\n";

        if ($phone) {
            $sendMessage .= "📞 Telefon raqam: {$phone}\n";
        }

        if ($message) {
            $sendMessage .= "📝 Habar matni: {$message}\n";
        }

        $sendMessage .= "📆 Buyurtma sanasi: {$time}";

        $url = "https://api.telegram.org/bot{$this->botToken}/" . ($file ? 'sendDocument' : 'sendMessage');

        if ($file) {
            $filename = 'file.' . pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

            Http::attach('document', $file, $filename)->post($url, [
                'chat_id' => $this->chatId,
                'caption' => $sendMessage,
            ]);
        } else {
            Http::post($url, [
                'chat_id' => $this->chatId,
                'text' => $sendMessage,
            ]);
        }
    }
}
