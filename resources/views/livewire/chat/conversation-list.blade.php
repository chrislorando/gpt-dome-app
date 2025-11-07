<div class="flex flex-col overflow-hidden">
    {{-- Conversation Items --}}
    <flux:navlist variant="outline" class="overflow-hidden">
        @forelse($conversations as $conversation)
            <flux:navlist.item 
                :href="route('chat.bot-ai.show', $conversation)"
                :current="request()->routeIs('chat.bot-ai.show') && request()->route('id') == $conversation->id"

                wire:navigate
                wire:key="conversation-{{ $conversation->id }}"
                class="group relative"
            >
                @php
                    $title = $conversation->title ?? 'New Conversation';
                @endphp
                <span class="truncate">
                    @if(\Illuminate\Support\Str::length($title) >= 30)
                        {{ \Illuminate\Support\Str::substr($title, 0, 25) . '...' }}
                    @else
                        {{ $title }} 
                    @endif
                </span>
                
                <button 
                    wire:click.prevent="deleteConversation({{ $conversation->id }})"
                    class="absolute right-2 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 p-1.5 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded transition-opacity"
                    title="Delete conversation"
                >
                    <flux:icon.trash class="w-3.5 h-3.5 text-red-500" />
                </button>
            </flux:navlist.item>
        @empty
            <div class="px-4 py-8 text-center text-zinc-400 dark:text-zinc-500 text-sm">
                No conversations yet
            </div>
        @endforelse
    </flux:navlist>
</div>
