<?php

namespace App\Providers;

use App\Services\Bots\JomMarbleBotService;
use App\Services\Bots\NasosBotService;
use App\Services\Bots\IgsBotService;
use App\Services\BotServiceInterface;
use Illuminate\Support\ServiceProvider;

class BotServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(BotServiceInterface::class, function ($app, $parameters) {
            $project_name = $parameters[0];

            $data = getProjectsJson();
            $data = $data['projects'][$project_name];

            $botToken = $data['bot_token'];
            $chatId = $data['chat_id'];

            switch ($project_name) {
                case 'JOM_MARBLE':
                    return new JomMarbleBotService($botToken, $chatId);
                case 'NASOS':
                    return new NasosBotService($botToken, $chatId);
                case 'IGS':
                    return new IgsBotService($botToken, $chatId);
                default:
                    throw new \InvalidArgumentException("No service found for project {$project_name}");
            }
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
