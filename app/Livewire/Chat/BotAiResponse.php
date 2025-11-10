<?php

namespace App\Livewire\Chat;

use App\Models\ConversationItem;
use App\Models\SharedConversation;
use App\Services\AiServiceInterface;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Str;

class BotAiResponse extends Component
{
    public string $prompt = '';

    public string $question = '';

    public string $answer = '';

    public string $content = '';

    public ?Collection $messages;

    public ?string $conversationId = null;

    public ?int $conversationItemId = null;

    public string $selectedModel = 'gpt-4o-mini';

    public string $shareUrl = '';

    public bool $copied = false;


    public function mount($conversationId = null)
    {
        $this->conversationId = $conversationId;
        $this->messages = ConversationItem::where('conversation_id', $this->conversationId)->whereNotNull('content')->orderBy('created_at')->get();

        // Check if there's an ongoing streaming response
        $inProgressMessage = $this->messages->where('status', \App\Enums\ResponseStatus::InProgress)->first();
        if ($inProgressMessage) {
            // Find the user message before this assistant message
            $userMessage = $this->messages->where('role', 'user')->last();
            if ($userMessage) {
                $this->question = $userMessage->content;
                $this->answer = $inProgressMessage->content;
            }
        }
    }

    public function getAiService(): AiServiceInterface
    {
        return app(AiServiceInterface::class);
    }

    #[On('ask-ai')]
    public function ask($conversationId, $question, $model)
    {
        $this->conversationId = $conversationId;
        $this->question = $question;
        $this->selectedModel = $model;

        $this->dispatch('scroll-down');
        $this->js('$wire.streamResponse()');
    }

    public function streamResponse()
    {
        $this->getAiService()->sendMessageWithStream(
            $this->conversationId,
            $this->question,
            function ($partial) {
                $this->content .= $partial;
                $this->stream(to: 'answer', content: $partial);
            },
            $this->selectedModel
        );

        $this->showChats();
    }

    #[On('show-chats')]
    public function showChats()
    {
        $lastMessages = ConversationItem::where('conversation_id', $this->conversationId)->limit(2)->latest()->get()->sortBy('created_at');
        foreach ($lastMessages as $row) {
            $this->messages->push($row);
        }

        // Only reset question if the last assistant message is completed
        $lastAssistant = $this->messages->where('role', 'assistant')->last();
        if ($lastAssistant && $lastAssistant->status === \App\Enums\ResponseStatus::Completed) {
            $this->question = '';
        }

        $this->dispatch('scroll-stop');
        $this->dispatch('loading-stop')->to(BotAi::class);
    }

    public function shareConversation(string $conversationItemId): void
    {
        $conversationItem = ConversationItem::find($conversationItemId);

        if (! $conversationItem) {
            return;
        }

        $content = [['role' => $conversationItem->role, 'content' => $conversationItem->content]];

        $hash = hash('sha256', json_encode($content));

        $sharedConversation = SharedConversation::where('content_hash', $hash)
            ->where('user_id', $conversationItem->conversation->user_id)
            ->first();

        if(!$sharedConversation){
            $sharedConversation = new SharedConversation();
            $sharedConversation->user_id = $conversationItem->conversation->user_id;
            $sharedConversation->content_hash = $hash;
            $sharedConversation->share_token = (string) Str::uuid();
            $sharedConversation->content = $content;
            $sharedConversation->title = $conversationItem->conversation->title;
            $sharedConversation->expires_at = null;
            $sharedConversation->save();
        }

        // Public link (route may be added later). Use a simple path for now.
        $this->shareUrl = url('/shared/'.$sharedConversation->share_token);
        $this->copied = false;
        $this->modal('share-modal')->show();
    }

    public function copyShareLink(): void
    {
        $this->js('navigator.clipboard.writeText("'.$this->shareUrl.'")');
        $this->copied = true;
    }

    public function render()
    {
        return view('livewire.chat.bot-ai-response');
    }
}
