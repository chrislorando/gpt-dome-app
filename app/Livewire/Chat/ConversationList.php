<?php

namespace App\Livewire\Chat;

use App\Models\Conversation;
use Livewire\Attributes\On;
use Livewire\Component;

class ConversationList extends Component
{
    public ?string $selectedConversationId = null;

    public string $search = '';

    public bool $showNewButton = false;

    public function openSearchModal(): void
    {
        $this->dispatch('open-search-modal');
    }

    public function deleteConversation(string $conversationId): void
    {
        Conversation::find($conversationId)?->delete();
        $this->dispatch('conversation-deleted');
    }

    #[On('conversation-created')]
    #[On('conversation-deleted')]
    public function refresh(): void
    {
        // Method to trigger re-render
    }

    public function render()
    {
        $conversations = Conversation::query()
            ->where('user_id', auth()->id())
            // ->has('items')
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->latest()
            ->get();

        return view('livewire.chat.conversation-list', [
            'conversations' => $conversations,
        ]);
    }
}
