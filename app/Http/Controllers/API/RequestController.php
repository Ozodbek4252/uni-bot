<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function requestToBot(Request $request)
    {
        if (!$request->project) {
            return response()->json(['error' => 'Project is required'], 400);
        }

        $project_name = strtoupper($request->project);

        $botToken = env($project_name . '_TG_BOT_TOKEN');
        $chatId = env($project_name . '_TG_CHAT_ID');

        $name = $request->input('name');
        $phone = $request->input('phone');
        $message = $request->input('message');
        $ip = $request->ip();
        $time = now()->format('d.m.Y - H:i');

        $send_message = "📞 Телефон: {$phone}\n";
        if ($name) {
            $send_message .= "👨‍💻 Имя: {$name}\n";
        }
        if ($message) {
            $send_message .= "📝 Сообщение: {$message}\n";
        }
        $send_message .= "IP: {$ip}\n⏰ Время заказа: {$time}";

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        Http::post($url, [
            'chat_id' => $chatId,
            'text' => $send_message,
        ]);
    }
}
