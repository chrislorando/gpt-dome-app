<flux:modal wire:model="showModal" class="min-w-[500px]">
    <flux:modal.trigger>
        {{-- Trigger is handled by the button in conversation-list --}}
    </flux:modal.trigger>

    <form class="space-y-6">
        <div>
            <flux:heading size="lg">Search Chats</flux:heading>
            <flux:subheading>Find your previous chats quickly</flux:subheading>
        </div>

        {{-- Search Input --}}
        <flux:input 
            wire:model.live.debounce.300ms="searchQuery" 
            placeholder="Type to search..." 
            autofocus
        >
            <x-slot name="iconLeading">
                <flux:icon.magnifying-glass />
            </x-slot>
        </flux:input>

        {{-- Search Results --}}
        <div class="max-h-[400px] space-y-2">
            @if(strlen($searchQuery) < 2)
                <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                    <flux:icon.magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p class="text-sm">Start typing to search chats...</p>
                </div>
            @elseif(count($searchResults) > 0)
                @foreach($searchResults as $result)
                    <div>
                        <flux:link variant="ghost" :href="route('chat.bot-ai.show', $result)"
                            wire:navigate
                            wire:key="conversation-{{ $result->id }}">
                            <flux:heading>{{ $result->title }}</flux:heading>
                            <flux:text class="mt-2">{{ $result->items->count() }} messages· {{ $result->updated_at->diffForHumans() }}</flux:text>
                        </flux:link>
                    </div>
                @endforeach
                
            @else
                <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                    <flux:icon.exclamation-circle class="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p class="text-sm">No conversations found</p>
                    <p class="text-xs mt-1">Try different keywords</p>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex gap-2 justify-end">
            <flux:button wire:click="closeModal" variant="ghost">Close</flux:button>
        </div>
    </form>
</flux:modal>
