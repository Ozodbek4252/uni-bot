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

        if ($project_name == 'JOM_MARBLE') {
            $project_name = 'JOM_MARBLE';
        } elseif ($project_name == 'NASOS') {
            $project_name = 'NASOS';
        } else {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $botToken = env($project_name . '_TG_BOT_TOKEN');
        $chatId = env($project_name . '_TG_CHAT_ID');

        $name = $request->input('name');
        $phone = $request->input('phone');
        $message = $request->input('message');
        $theme = $request->input('theme');
        $time = now()->format('d.m.Y - H:i');

        $send_message = "📞 Телефон: {$phone}\n";
        if ($name) {
            $send_message .= "👨‍💻 Имя: {$name}\n";
        }
        if ($message) {
            $send_message .= "📝 Сообщение: {$message}\n";
        }
        if ($theme) {
            $send_message .= "📦 Тема: {$theme}\n";
        }
        $send_message .= "⏰ Время заказа: {$time}";

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        Http::post($url, [
            'chat_id' => $chatId,
            'text' => $send_message,
        ]);
    }
}
