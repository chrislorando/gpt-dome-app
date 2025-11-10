<?php

namespace App\Livewire\Chat;

use App\Models\Conversation;
use App\Models\SharedConversation;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Str;

class ConversationList extends Component
{
    public ?string $selectedConversationId = null;

    public string $search = '';

    public bool $showNewButton = false;

    public ?string $renamingConversationId = null;

    public string $title = '';

    public ?string $deletingConversationId = null;

    public string $shareUrl = '';

    public bool $copied = false;

    public function openSearchModal(): void
    {
        $this->dispatch('open-search-modal');
    }

    public function shareConversation(string $conversationId): void
    {
        $conversation = Conversation::with('items')->find($conversationId);

        if (! $conversation) {
            return;
        }

        // Prepare messages from conversation items (preserve order)
        $messages = $conversation->items->sortBy('created_at')->map(function ($item) {
            return [
                'role' => $item->role,
                'content' => $item->content,
            ];
        })->values()->toArray();

        $hash = hash('sha256', json_encode($messages));

        $sharedConversation = SharedConversation::where('content_hash', $hash)
            ->where('user_id', $conversation->user_id)
            ->first();

        if(!$sharedConversation){
            $sharedConversation = new SharedConversation();
            $sharedConversation->user_id = $conversation->user_id;
            $sharedConversation->content_hash = $hash;
            $sharedConversation->share_token = (string) Str::uuid();
            $sharedConversation->content = $messages;
            $sharedConversation->title = $conversation->title;
            $sharedConversation->expires_at = null;
            $sharedConversation->save();
        }

        // Public link (route may be added later). Use a simple path for now.
        $this->shareUrl = url('/shared/'.$sharedConversation->share_token);
        $this->copied = false;
        $this->modal('share-modal')->show();
    }

    public function renameConversation(string $conversationId): void
    {
        $this->renamingConversationId = $conversationId;
        $conversation = Conversation::find($conversationId);
        $this->title = $conversation->title ?? 'New Conversation';
        $this->modal('rename-modal')->show();
    }

    public function saveRename(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
        ]);

        $conversation = Conversation::find($this->renamingConversationId);
        $conversation->update(['title' => $this->title]);
        $this->modal('rename-modal')->close();
        $this->dispatch('conversation-updated');
    }

    public function cancelRename(): void
    {
        $this->modal('rename-modal')->close();
    }

    public function copyShareLink(): void
    {
        $this->js('navigator.clipboard.writeText("'.$this->shareUrl.'")');
        $this->copied = true;
    }

    public function deleteConversation(string $conversationId): void
    {
        $this->deletingConversationId = $conversationId;
        $this->modal('delete-modal')->show();
    }

    public function confirmDelete(): void
    {
        Conversation::find($this->deletingConversationId)?->delete();
        $this->modal('delete-modal')->close();
        $this->dispatch('conversation-deleted');
    }

    public function cancelDelete(): void
    {
        $this->modal('delete-modal')->close();
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
