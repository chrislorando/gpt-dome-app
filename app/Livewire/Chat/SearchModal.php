<?php

namespace App\Livewire\Chat;

use App\Models\Conversation;
use Livewire\Attributes\On;
use Livewire\Component;

class SearchModal extends Component
{
    public bool $showModal = false;

    public string $searchQuery = '';

    public function mount(): void
    {
        $this->showModal = false;
        $this->searchQuery = '';
    }

    #[On('open-search-modal')]
    public function openModal(): void
    {
        $this->showModal = true;
        $this->searchQuery = '';
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->searchQuery = '';
    }

    public function selectConversation(string $conversationId): void
    {
        $this->dispatch('conversation-selected', conversationId: $conversationId);
        $this->closeModal();
    }

    public function render()
    {
        $searchResults = [];

        if (strlen($this->searchQuery) >= 2) {
            $searchResults = Conversation::query()
                ->where('user_id', auth()->id())
                ->where(function ($query) {
                    $query->search($this->searchQuery)
                        ->orWhereHas('items', function ($q) {
                            $q->where('content', 'like', '%' . $this->searchQuery . '%');
                        });
                })
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('livewire.chat.search-modal', [
            'searchResults' => $searchResults,
        ]);
    }
}
