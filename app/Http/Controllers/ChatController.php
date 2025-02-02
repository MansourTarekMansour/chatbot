<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use OpenAI;

class ChatController extends Controller
{
    // Get all chats
    public function index()
    {
        return response()->json(Chat::with('messages')->get());
    }

    // Create a new chat
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string', // For chat title or description
        ]);

        // Create a new chat instance and save it
        $chat = Chat::create([
            'title' => $validated['title'],
        ]);

        return response()->json($chat, 201); // Return the created chat
    }

    // Get messages for a specific chat
    public function show(Chat $chat)
    {
        return response()->json($chat->load('messages'));
    }

    // Send a message for the chat
    public function sendMessage(Request $request, Chat $chat)
    {
        $request->validate([
            'content' => 'required|string',
            'role' => 'required|in:user,assistant',
        ]);

        // Store the user's message
        $message = $chat->messages()->create([
            'content' => $request->content,
            'role' => $request->role,
        ]);

        // If the role is 'user', send it to OpenAI for a response
        if ($request->role === 'user') {
            $assistantResponse = $this->sendToOpenAI($request->content, $chat);

            // Optionally return the assistant's response
            return response()->json([
                'message' => $message,
                'assistant_response' => [
                    'content' => $assistantResponse,
                    'role' => 'assistant',
                ],
            ]);
        }

        return response()->json($message);
    }



    // Send message to OpenAI and save assistant response
    private function sendToOpenAI(string $userInput, Chat $chat)
    {
        try {
            // Retrieve the OpenAI API key from the environment
            $openaiApiKey = env('OPENAI_API_KEY');

            // Ensure the API key is not null
            if (empty($openaiApiKey)) {
                throw new \Exception('OPENAI_API_KEY is not set in the environment.');
            }
            $client = OpenAI::factory()
                ->withBaseUri('https://models.inference.ai.azure.com') // Use the OpenAI API base URL
                ->withHttpHeader('api-key', $openaiApiKey) // Use the OpenAI API key
                ->make();

            // Call the OpenAI API to get a response
            $result = $client->chat()->create([
                'model' => 'gpt-4o', // Ensure to use the correct model
                'messages' => [
                    ['role' => 'user', 'content' => $userInput],
                ],
                'temperature' => 1,
                'max_tokens' => 4096,
                'top_p' => 1,
            ]);

            // Extract the assistant's response
            $assistantResponse = $result->choices[0]->message->content;

            // Store the assistant's response in the chat
            $chat->messages()->create([
                'content' => $assistantResponse,
                'role' => 'assistant',
            ]);

            return $assistantResponse; // Return the response to be sent back to the client
        } catch (\Exception $e) {
            // Log error and handle API error
            \Log::error('OpenAI API Error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'API error: ' . $e->getMessage()], 500);
        }
    }
}
