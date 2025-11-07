<?php

namespace App\Livewire\Chat;

use App\Models\ConversationItem;
use App\Services\AiServiceInterface;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatBotAiResponse extends Component
{
public string $prompt = '';

    public string $question = '';

    public string $answer = '';
    
    public string $content = '';

    public ?Collection $messages;

    public ?string $conversationId = null;

    public ?int $conversationItemId = null;

    public string $selectedModel = 'gpt-4o-mini';


    public function mount($conversationId=null)
    {
        $this->conversationId = $conversationId;
        $this->messages = ConversationItem::where('conversation_id', $this->conversationId)->orderBy('created_at')->get();

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
        foreach($lastMessages as $row){
            $this->messages->push($row);
        }
        
        // Only reset question if the last assistant message is completed
        $lastAssistant = $this->messages->where('role', 'assistant')->last();
        if ($lastAssistant && $lastAssistant->status === \App\Enums\ResponseStatus::Completed) {
            $this->question = '';
        }
        
        $this->dispatch('scroll-stop');
        $this->dispatch('loading-stop')->to(ChatBotAi::class);
    }


    public function render()
    {
        return view('livewire.chat.chat-bot-ai-response');
    }
}
