<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\BotServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class RequestController extends Controller
{
    public function requestToBot(Request $request)
    {
        if (!$request->project) {
            return response()->json(['error' => 'Project is required'], 400);
        }

        $project_name = strtoupper($request->project);

        $projects = getProjectsJson()['project_list'];

        if (!in_array($project_name, $projects)) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Fetch the bot service using the project name
        $botService = App::make(BotServiceInterface::class, [$project_name]);

        // Send message
        $botService->sendMessage($request->all());

        return response()->json(['success' => 'Message sent']);
    }
}
