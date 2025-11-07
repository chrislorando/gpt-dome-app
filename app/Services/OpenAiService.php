<?php

namespace App\Services;

use App\Enums\ResponseStatus;
use App\Models\Conversation;
use App\Models\ConversationItem;
use App\Services\ChatService;
use Exception;
use Log;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class OpenAiService implements AiServiceInterface
{
    public function sendMessageWithStream(string $conversationId, string $content, callable $onChunk, ?string $model = null)
    {
        $model = $model ?? 'gpt-4o-mini';
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

        // Save assistant message
        $chatService = app(ChatService::class);
        $responseId = null; // Initialize responseId
        $assistantMessage = $chatService->createAssistantMessage($conversationId, '', $model, $responseId);

        // Set status to in_progress before streaming
        $assistantMessage->update(['status' => ResponseStatus::InProgress]);

        try {
            Log::info('Starting OpenAI streaming', ['messages_count' => count($messages)]);
            // Call OpenAI API with streaming using createStreamed()
            $stream = OpenAI::chat()->createStreamed([
                'model' => $model,
                'messages' => $messages ?? $content,
            ]);

            $assistantContent = '';
            $chunkCount = 0;

            foreach ($stream as $response) {
                if ($responseId === null && isset($response->id)) {
                    $responseId = $response->id;
                }
                $chunk = $response->choices[0]->delta->content ?? '';
                if ($chunk) {
                    $assistantContent .= $chunk;
                    $chunkCount++;
                    // Log::info("Chunk {$chunkCount}: " . substr($chunk, 0, 50) . '...');
                    $onChunk($chunk);
                }
            }

            Log::info("Streaming completed with {$chunkCount} chunks, total content length: " . strlen($assistantContent));

            // Update status to completed and set content and response_id
            $assistantMessage->update(['status' => ResponseStatus::Completed, 'content' => $assistantContent, 'response_id' => $responseId]);
        } catch (Throwable $e) {
            // Update status to failed on error
            $assistantMessage->update(['status' => ResponseStatus::Failed]);

            Log::info($messages);

            $errorMessage = 'Failed to get response from OpenAI: '.$e->getMessage();

            if (str_contains($e->getMessage(), 'API key') || str_contains($e->getMessage(), 'api_key')) {
                $errorMessage = 'OpenAI API key is not configured. Please add your API key to the .env file.';
            } elseif (str_contains($e->getMessage(), 'quota') || str_contains($e->getMessage(), 'exceeded')) {
                $errorMessage = 'OpenAI quota exceeded. Please check your OpenAI account or subscription.';
            } elseif (str_contains($e->getMessage(), 'authentication') || str_contains($e->getMessage(), '401')) {
                $errorMessage = 'OpenAI authentication failed. Please verify your API key is valid.';
            } elseif (str_contains($e->getMessage(), 'rate limit') || str_contains($e->getMessage(), '429')) {
                $errorMessage = 'OpenAI rate limit reached. Please try again later.';
            } elseif (str_contains($e->getMessage(), 'model') || str_contains($e->getMessage(), '404')) {
                $errorMessage = 'OpenAI model not found or not available.';
            }

            throw new Exception($errorMessage);
        }

        return $assistantMessage;
    }

    public function cancelResponse(string $responseId): array
    {
        try {
            Log::info('Cancelling OpenAI response', ['response_id' => $responseId]);

            $response = OpenAI::responses()->cancel($responseId);

            Log::info('Successfully cancelled OpenAI response', ['response_id' => $response->id, 'status' => $response->status]);

            return [
                'id' => $response->id,
                'status' => $response->status,
            ];
        } catch (Throwable $e) {
            $errorMessage = 'Failed to cancel OpenAI response: ' . $e->getMessage();

            Log::error($errorMessage, ['response_id' => $responseId]);

            throw new Exception($errorMessage);
        }
    }

    public function fetchModels(): array
    {
        try {
            Log::info('Fetching models from OpenAI');

            $response = OpenAI::models()->list();

            $models = [];
            foreach ($response->data as $model) {
                $models[] = [
                    'id' => $model->id,
                    'object' => $model->object,
                    'owned_by' => $model->owned_by ?? 'openai',
                ];
            }

            Log::info('Successfully fetched ' . count($models) . ' models from OpenAI');

            return $models;
        } catch (Throwable $e) {
            $errorMessage = 'Failed to fetch models from OpenAI: ' . $e->getMessage();

            if (str_contains($e->getMessage(), 'API key') || str_contains($e->getMessage(), 'api_key')) {
                $errorMessage = 'OpenAI API key is not configured. Please add your API key to the .env file.';
            } elseif (str_contains($e->getMessage(), 'authentication') || str_contains($e->getMessage(), '401')) {
                $errorMessage = 'OpenAI authentication failed. Please verify your API key is valid.';
            } elseif (str_contains($e->getMessage(), 'rate limit') || str_contains($e->getMessage(), '429')) {
                $errorMessage = 'OpenAI rate limit reached. Please try again later.';
            }

            Log::error($errorMessage);
            throw new Exception($errorMessage);
        }
    }
}