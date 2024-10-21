<?php

namespace App\Services\Bots;

use App\Services\BotServiceInterface;
use Illuminate\Support\Facades\Http;

class QuyoshliBotService implements BotServiceInterface
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
        $order_id = $data['order_id'] ?? null;
        $client_type = $data['client_type'] ?? null;
        $delivery_type = $data['delivery_type'] ?? null;
        $summa = $data['summa'] ?? null;
        $time = now()->timezone('Asia/Tashkent')->format('Y-m-d H:i:s');
        $group_id = $data['group_id'] ?? null;

        $sendMessage = "📬 Номер заказа: {$order_id}\n";
        if ($client_type) {
            $sendMessage .= "💼 Тип клиента: {$client_type}\n";
        }
        if ($delivery_type) {
            $sendMessage .= "🚗 Тип доставки: {$delivery_type}\n";
        }
        if ($summa) {
            $summa = number_format($summa, 0, '.', ' ');
            $sendMessage .= "💰 Сумма заказа: {$summa} сум\n";
        }
        $sendMessage .= "📆 Дата заказа: {$time}";

        if ($group_id) {
            $this->chatId = $group_id;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        Http::post($url, [
            'chat_id' => $this->chatId,
            'text' => $sendMessage,
        ]);
    }
}
