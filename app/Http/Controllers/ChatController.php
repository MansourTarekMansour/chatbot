<?php

namespace App\Http\Controllers;

use OpenAI;
use OpenAI\Client;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index()
    {
        return Chat::with('messages')->get();
    }

    public function store()
    {
        $chat = Chat::create(['title' => 'New Chat']);
        return response()->json($chat);
    }

    public function show(Chat $chat)
    {
        return $chat->load('messages');
    }

    public function destroy(Chat $chat)
    {
        $chat->delete();
        return response()->json(null, 204);
    }

    public function sendMessage(Request $request, Chat $chat)
    {
        $request->validate(['content' => 'required|string']);
        
        // Save user message
        $message = $chat->messages()->create([
            'content' => $request->content,
            'role' => 'user'
        ]);

        // Get AI response
        $response = $this->getOpenAIResponse($request->content);
        
        // Save assistant message
        $assistantMessage = $chat->messages()->create([
            'content' => $response,
            'role' => 'assistant'
        ]);

        return response()->json([
            'user_message' => $message,
            'assistant_response' => $assistantMessage
        ]);
    }

    private function getOpenAIResponse(string $prompt): string
    {
        try {
            $client = $this->createOpenAIClient();
            
            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => ''],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 1,
                'max_tokens' => 4096,
                'top_p' => 1
            ]);

            return $response->choices[0]->message->content;

        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }

    private function createOpenAIClient(): Client
    {
    $token = env('GITHUB_TOKEN');
    
    if (empty($token)) {
        throw new \RuntimeException('GitHub token not configured in .env file');
    }

        return OpenAI::factory()
            ->withBaseUri('https://models.inference.ai.azure.com')
            ->withHttpHeader('api-key', $token)
            ->make();
    }
}