<?php

namespace App\Services\Bots;

use GuzzleHttp\Exception\RequestException;
use App\Services\BotServiceInterface;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class IgsBotService implements BotServiceInterface
{
    protected $botToken;
    protected $chatId;
    protected $url;
    protected $sendMessage;

    /**
     * Constructor for IgsBotService.
     *
     * @param string $botToken Telegram bot token.
     * @param string $chatId Telegram chat ID.
     */
    public function __construct($botToken, $chatId)
    {
        $this->botToken = $botToken;
        $this->chatId = $chatId;
        $this->url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
    }

    /**
     * Sends a message to Telegram based on the provided data.
     *
     * @param array $data Message data containing type, file, name, phone, and other optional fields.
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(array $data)
    {
        $this->sendMessage = $this->createMessageContent($data);

        if ($this->isFileTypeAllowed($data['file'] ?? null, $data['type'] ?? null)) {
            $this->url = "https://api.telegram.org/bot{$this->botToken}/sendDocument";
            return $this->sendDocument($data['file']);
        }

        return $this->sendTextMessage();
    }

    /**
     * Creates the message content based on the type of request.
     *
     * @param array $data Message data.
     * @return string
     */
    private function createMessageContent(array $data): string
    {
        $type = $data['type'] ?? null;
        $phone = $data['phone'] ?? null;
        $name = $data['name'] ?? null;
        $time = now()->format('d.m.Y - H:i');

        $message = $this->getMessageTypeHeader($type);

        $message .= "📞 Телефон: {$phone}\n";

        if ($name) {
            $message .= "👨‍💻 Имя: {$name}\n";
        }

        $message .= $this->getAdditionalMessageContent($data, $type);
        $message .= "⏰ Время: {$time}";

        return $message;
    }

    /**
     * Returns the message header based on the type.
     *
     * @param string|null $type Type of the request.
     * @return string
     */
    private function getMessageTypeHeader(?string $type): string
    {
        switch ($type) {
            case 'tour':
                return "Заявка на тур\n\n";
            case 'vacancy':
                return "Заявка на вакансию\n\n";
            case 'admission':
                return "Заявка на поступление\n\n";
            default:
                return "";
        }
    }

    /**
     * Returns additional message content based on the type.
     *
     * @param array $data Message data.
     * @param string|null $type Type of the request.
     * @return string
     */
    private function getAdditionalMessageContent(array $data, ?string $type): string
    {
        $content = "";

        if ($type === 'vacancy') {
            $content .= $this->formatVacancyContent($data);
        } elseif ($type === 'admission') {
            $content .= $this->formatAdmissionContent($data);
        }

        return $content;
    }

    /**
     * Formats additional content for vacancy type messages.
     *
     * @param array $data Message data.
     * @return string
     */
    private function formatVacancyContent(array $data): string
    {
        $content = "";
        $position = $data['theme'] ?? null;
        $email = $data['email'] ?? null;
        $description = $data['description'] ?? null;

        if ($position) {
            $content .= "👨‍💼 Позиция: {$position}\n";
        }
        if ($email) {
            $content .= "📧 Эл. почта: {$email}\n";
        }
        if ($description) {
            $content .= "📝 Описание: {$description}\n";
        }

        return $content;
    }

    /**
     * Formats additional content for admission type messages.
     *
     * @param array $data Message data.
     * @return string
     */
    private function formatAdmissionContent(array $data): string
    {
        $content = "";
        $edu_type = $data['edu_type'] ?? null;
        $english_level = $data['english_level'] ?? null;
        $date = $data['date'] ?? null;
        $grade = $data['grade'] ?? null;

        if ($edu_type) {
            $content .= "🧾 Тип образования: {$edu_type}\n";
        }
        if ($english_level) {
            $content .= "📘 Уровень английского: {$english_level}\n";
        }
        if ($date) {
            $content .= "📅 Дата: {$date}\n";
        }
        if ($grade) {
            $content .= "👨‍🎓 Описание: {$grade}\n";
        }

        return $content;
    }

    /**
     * Checks if the provided file type is allowed.
     *
     * @param mixed $file File object or null.
     * @param string|null $type Type of the request.
     * @return bool
     */
    private function isFileTypeAllowed($file, ?string $type): bool
    {
        if ($type !== 'vacancy' || !$file) {
            return false;
        }

        $allowedExtensions = ['doc', 'docx', 'pdf'];
        return in_array($file->extension(), $allowedExtensions);
    }

    /**
     * Sends a text message to Telegram.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function sendTextMessage()
    {
        Http::post($this->url, [
            'chat_id' => $this->chatId,
            'text' => $this->sendMessage,
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Sends a document to Telegram.
     *
     * @param mixed $file File object.
     * @return \Illuminate\Http\JsonResponse
     */
    private function sendDocument($file)
    {
        $client = new Client();

        $multipartData = [
            [
                'name'     => 'chat_id',
                'contents' => $this->chatId,
            ],
            [
                'name'     => 'document',
                'contents' => fopen($file->getPathname(), 'r'),
                'filename' => $file->getClientOriginalName(),
            ],
            [
                'name'     => 'caption',
                'contents' => $this->sendMessage,
            ],
        ];

        try {
            $response = $client->post($this->url, [
                'multipart' => $multipartData,
            ]);

            return response()->json([
                'status' => 'success',
                'response' => json_decode($response->getBody()->getContents(), true)
            ]);
        } catch (RequestException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getResponse()->getStatusCode());
        }
    }
}
