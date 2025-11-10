<?php

namespace App\Livewire\Chat;

use App\Models\SharedConversation as SharedConversationModel;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public.clear')]
class SharedConversation extends Component
{
    public string $token;

    public array $contents = [];

    public ?string $title = null;

    public ?\DateTime $expiresAt = null;

    public function mount(string $token): void
    {
        $this->token = $token;

        $shared = SharedConversationModel::where('share_token', $token)->firstOrFail();

        $this->contents = $shared->content ?? [];
        $this->title = $shared->title;
        $this->expiresAt = $shared->expires_at ? $shared->expires_at->toDateTime() : null;
    }

    public function render()
    {
        return view('livewire.chat.shared-conversation', [
            'contents' => $this->contents,
            'title' => $this->title,
            'expiresAt' => $this->expiresAt,
        ]);
    }
}
