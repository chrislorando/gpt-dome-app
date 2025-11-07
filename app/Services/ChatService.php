<?php

namespace App\Services;

use App\Enums\ResponseStatus;
use App\Models\Conversation;
use App\Models\ConversationItem;

class ChatService
{
    public function createUserMessage(string $conversationId, string $content): ConversationItem
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Save user message
        $userMessage = $conversation->items()->create([
            'role' => 'user',
            'content' => $content,
        ]);

        // Update conversation title if empty (use first user message)
        if (! $conversation->title) {
            $conversation->update([
                'title' => $this->generateTitle($content),
            ]);
        }

        return $userMessage;
    }

    public function createConversation(?int $userId = null): Conversation
    {
        return Conversation::create([
            'user_id' => $userId,
            'title' => null,
        ]);
    }

    public function createAssistantMessage(string $conversationId, string $content, ?string $modelId = null, ?string $responseId = null): ConversationItem
    {
        $conversation = Conversation::findOrFail($conversationId);

        return $conversation->items()->create([
            'role' => 'assistant',
            'content' => $content,
            'model_id' => $modelId,
            'response_id' => $responseId,
            'status' => ResponseStatus::Created,
        ]);
    }

    public function getConversationMessages(string $conversationId): array
    {
        $conversation = Conversation::findOrFail($conversationId);

        return $conversation->items()
            ->orderBy('created_at')
            ->get()
            ->map(fn (ConversationItem $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->toArray();
    }

    public function changeStatus(string $conversationId, string $status)
    {
        $conversation = ConversationItem::where('conversation_id', $conversationId)
        ->whereIn('status', [ResponseStatus::Created, ResponseStatus::InProgress]);

        $conversation->update(['status' => $status]);

        return $conversation;
    }

    private function generateTitle(string $content): string
    {
        // Generate title from first 50 characters of first message
        return substr(ucfirst($content), 0, 50).(strlen($content) > 50 ? '...' : '');
    }
}
