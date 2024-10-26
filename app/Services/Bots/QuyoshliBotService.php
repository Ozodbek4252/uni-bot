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
        $group_id = $data['group_id'] ?? null;
        $message = $data['message'] ?? null;
        $order_url = $data['order_url'] ?? null;
        $client = $data['client'] ?? null;
        $phone = $data['phone'] ?? null;
        $products = $data['products'] ?? null;
        $address = $data['address'] ?? null;
        $file = $data['file'] ?? null;
        $payment_type = $data['payment_type'] ?? null;
        $time = now()->timezone('Asia/Tashkent')->format('Y-m-d H:i:s');

        //! Habar matni:
        //! To'liq ma'lumot: URL
        //! Buyurtmachi: Palonchiyev Pistonchi (Yuridik shaxs)
        //! Telefon raqam: +998 99 XXX XX XX
        //! Buyurtma raqami: XXXXXX
        //! Buyurtma mahsulotlar va ularning narxlari:
        //! Mahsulot 1 - 15 000 000 so'm
        //! Mahsulot 2 - 30 000 000 so'm

        // validation for file if exists
        if ($file) {
            if (!file_exists($file)) {
                return response()->json(['error' => 'File not found'], 404);
            }
        }

        //! Yetkazib berishi turi:
        //! Manzil: Palonchi viloyat, Palonchi tuman , adress va uy raqami

        $sendMessage = "🆔 Buyurtma raqami: {$order_id}\n";

        if ($message) {
            $sendMessage .= "📝 Habar matni: {$message}\n";
        }

        if ($order_url) {
            $sendMessage .= "🔗 To'liq ma'lumot: {$order_url}\n";
        }

        if ($client) {
            $sendMessage .= "👤 Buyurtmachi: {$client}\n";
        }

        if ($phone) {
            $sendMessage .= "📞 Telefon raqam: {$phone}\n";
        }

        if ($products) {
            // check if products is array
            if (is_array($products)) {
                $summa = 0;
                $sendMessage .= "💵 Buyurtma mahsulotlar va ularning narxlari: \n";
                foreach ($products as $product) {
                    if (isset($product['name']) && isset($product['price']) && isset($product['count'])) {
                        $sendMessage .= "📦 Mahsulot: {$product['name']} - {$product['price']} so'm - {$product['count']} ta\n";
                    }
                    $summa += $product['price'] * $product['count'];
                }
                $summa = number_format($summa, 0, '.', ' ');
                $sendMessage .= "💰 Summa: {$summa} so'm\n";
            }
        }

        if ($delivery_type) {
            $sendMessage .= "🚚 Yetkazib berishi turi: {$delivery_type}\n";
        }

        if ($client_type) {
            $sendMessage .= "💼 Тип клиента: {$client_type}\n";
        }

        if ($address) {
            $sendMessage .= "🌐 Manzil: {$address}\n";
        }

        if ($payment_type) {
            $sendMessage .= "💳 To'lov turi: {$payment_type}\n";
        }

        $sendMessage .= "📆 Buyurtma sanasi: {$time}";

        if ($group_id) {
            $this->chatId = $group_id;
        }

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
