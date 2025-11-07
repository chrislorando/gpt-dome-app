<?php

namespace App\Livewire\Chat;

use App\Enums\ResponseStatus;
use App\Models\ConversationItem;
use App\Services\AiServiceInterface;
use App\Services\ChatService;
use App\Models\AiModel;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app.full')]
class ChatBotAi extends Component
{    
    public string $prompt = '';

    public string $question = '';

    public string $answer = '';
    
    public string $content = '';

    public ?Collection $messages;

    public ?string $conversationId = null;

    public ?int $conversationItemId = null;

    public string $selectedModel = 'gpt-4o-mini';

    public Collection $activeModels;

    // When true, instruct the client to call submitPrompt after navigation completes
    public bool $submitOnNavigate = false;


    public function mount($id=null)
    {
        $this->conversationId = $id;
        $this->activeModels = AiModel::where('status', 'active')->get();

        // Check if there's an ongoing streaming response
        $inProgressMessage = ConversationItem::where('conversation_id', $this->conversationId)->where('status', ResponseStatus::InProgress)->first();
        if ($inProgressMessage) {
            // Find the user message before this assistant message
            $userMessage = ConversationItem::where('conversation_id', $this->conversationId)->where('role', 'user')->orderBy('created_at', 'desc')->first();
            if ($userMessage) {
                $this->question = $userMessage->content;
                $this->answer = $inProgressMessage->content;
            }
        }

        if (session()->has('selected_model')) {
            
            $this->selectedModel = session('selected_model');
        }

        // If there's an initial prompt from session (for new chat), set it and submit
        if (session()->has('initial_prompt')) {
            // Don't call submitPrompt() here — the child response component may not
            // yet be mounted. Instead set a flag so the client will call submit
            // after navigation completes (ensuring the response component exists).
            $this->prompt = session('initial_prompt');
            $this->selectedModel = session('initial_model', $this->selectedModel);
            session()->forget(['initial_prompt', 'initial_model']);
            $this->submitOnNavigate = true;
        }

        $this->dispatch('chat-navigated');
    }


    public function getAiService(): AiServiceInterface
    {
        return app(AiServiceInterface::class);
    }

    public function submitPrompt()
    {
        if (trim($this->prompt) === '') {
            return;
        }

        // Prevent submit if already streaming
        if (!empty($this->question)) {
            return;
        }

        // If no conversation yet, create one and redirect with prompt in session
        if ($this->conversationId === null) {
            $conversation = app(ChatService::class)->createConversation(auth()->id());
            session(['initial_prompt' => $this->prompt, 'initial_model' => $this->selectedModel]);
            $this->prompt = '';
            return $this->redirect(route('chat.bot-ai.show', $conversation->id), true); 
        }

        $this->question = $this->prompt;
        $this->prompt = '';

        // Save user message
        $userMessage = app(ChatService::class)->createUserMessage($this->conversationId, $this->question);
        $this->conversationItemId = $userMessage->id;

        $this->dispatch('conversation-created');
        $this->dispatch('ask-ai', conversationId:$this->conversationId, question:$this->question, model:$this->selectedModel);

    }


    #[On('loading-stop')]
    public function loadingStop()
    {
        $this->question = '';
        $this->dispatch('scroll-stop');
    }

    public function setSelectedModel($id, $label)
    {
            
        $this->selectedModelLabel = $label;
        session(['selected_model' => $this->selectedModel]);
    }

    public function updatedSelectedModel($value){
        $this->selectedModel = $value;
        session(['selected_model' => $value]);
    }


    public function cancel()
    {
        $this->reset('prompt', 'question', 'answer');
        $this->dispatch('scroll-stop');
        $this->dispatch('stream-stop');
        $this->dispatch('show-chats');
        session()->forget(['initial_prompt', 'initial_model']);
        app(ChatService::class)->changeStatus($this->conversationId, ResponseStatus::Incomplete->value);
        return $this->redirect(route('chat.bot-ai.show', $this->conversationId), true);
    }

    public function refresh(){}

    public function render()
    {
        return view('livewire.chat.chat-bot-ai');
    }
}
