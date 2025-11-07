<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationItem;
use Exception;
use Log;
use Throwable;

class GeminiService implements AiServiceInterface
{
    public function sendMessageWithStream(string $conversationId, string $content, callable $onChunk)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Get all messages for context
        $messages = $conversation->items()
            ->orderBy('created_at')
            ->get()
            ->map(fn (ConversationItem $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->toArray();

        try {
            Log::info('Starting Gemini streaming', ['messages_count' => count($messages)]);

            // TODO: Implement Gemini API streaming
            // This is a placeholder implementation
            // You would need to install Google AI SDK and configure API key
            //
            // Example:
            // use Google\AI\Client;
            //
            // $client = new Client(['apiKey' => config('services.gemini.api_key')]);
            // $response = $client->generateContent([
            //     'contents' => $messages,
            //     'generationConfig' => [
            //         'temperature' => 0.7,
            //         'maxOutputTokens' => 2048,
            //     ],
            // ]);
            //
            // For streaming, you would use streamingGenerateContent() method

            // Placeholder: simulate streaming for now
            $assistantContent = "This is a placeholder response from Gemini. Please implement the actual Gemini API integration.";

            // Simulate chunked response
            $chunks = str_split($assistantContent, 10);
            foreach ($chunks as $chunk) {
                $onChunk($chunk);
                usleep(50000); // 50ms delay to simulate streaming
            }

            Log::info("Gemini streaming simulation completed");
        } catch (Throwable $e) {
            $errorMessage = 'Failed to get response from Gemini: '.$e->getMessage();

            if (str_contains($e->getMessage(), 'API key')) {
                $errorMessage = 'Gemini API key is not configured. Please add your API key to the .env file.';
            } elseif (str_contains($e->getMessage(), 'quota')) {
                $errorMessage = 'Gemini quota exceeded. Please check your Google Cloud account.';
            } elseif (str_contains($e->getMessage(), 'authentication')) {
                $errorMessage = 'Gemini authentication failed. Please verify your API key is valid.';
            } elseif (str_contains($e->getMessage(), 'rate limit')) {
                $errorMessage = 'Gemini rate limit reached. Please try again later.';
            }

            throw new Exception($errorMessage);
        }

        // Save assistant message
        $assistantMessage = $conversation->items()->create([
            'role' => 'assistant',
            'content' => $assistantContent,
        ]);

        return $assistantMessage;
    }
}