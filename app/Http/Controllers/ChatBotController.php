<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OpenAI;

class ChatBotController extends Controller
{
    public function sendchat(Request $request)
    {
        // Validate the input to ensure it exists and is a string
        $request->validate([
            'input' => 'required|string',
        ]);

        try {
            // Retrieve the GitHub token from the environment
            $githubToken = env('GITHUB_TOKEN');

            // Ensure the token is not null
            if (empty($githubToken)) {
                throw new \Exception('GITHUB_TOKEN is not set in the environment.');
            }

            // Manually create the OpenAI client with custom configuration
            $client = OpenAI::factory()
                ->withBaseUri('https://models.inference.ai.azure.com') // Custom base URL
                ->withHttpHeader('api-key', $githubToken) // Use your GitHub token
                ->make();

            // Call the OpenAI API to get a completion
            $result = $client->chat()->create([
                'model' => 'gpt-4o', // Use the GPT-4 model
                'messages' => [
                    ['role' => 'system', 'content' => ''], // System message (optional)
                    ['role' => 'user', 'content' => $request->input('input')], // User message
                ],
                'temperature' => 1,
                'max_tokens' => 4096,
                'top_p' => 1,
            ]);

            // Extract the response from the result
            $response = $result->choices[0]->message->content;

            // Return the response as JSON
            return response()->json(['response' => trim($response)]);
        } catch (\Exception $e) {
            // Handle the error gracefully
            return response()->json(['error' => 'API error: ' . $e->getMessage()], 500);
        }
    }
}