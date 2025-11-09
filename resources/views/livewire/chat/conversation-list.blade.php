<div>
<div class="flex flex-col overflow-visible">
    {{-- Conversation Items --}}
    <flux:navlist variant="outline" class="overflow-visible">
        @forelse($conversations as $conversation)
            <div class="relative">
                <flux:navlist.item 
                    :href="route('chat.bot-ai.show', $conversation)"
                    :current="request()->routeIs('chat.bot-ai.show') && request()->route('id') == $conversation->id"

                    wire:navigate
                    wire:key="conversation-{{ $conversation->id }}"
                    class="pr-10"
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

                </flux:navlist.item>

                {{-- Hover-only actions dropdown using FluxUI dropdown/menu (outside the nav item link) --}}
                <div class="absolute right-2 top-1/2 -translate-y-1/2">

                    <flux:dropdown class="inline-block" position="bottom" align="end">
                        {{-- Trigger: use Flux button with trailing ellipsis icon (visible always) --}}
                        <flux:button variant="ghost" icon:trailing="ellipsis-vertical" title="Actions" />

                        {{-- Menu: use Flux's built-in behavior (click-to-open). No CSS hover show/hide. --}}
                        <flux:menu class="w-40 bg-white dark:bg-zinc-800 rounded-md shadow-lg z-50 ring-1 ring-black ring-opacity-5">
                            <flux:menu.item as="button" icon="share" wire:click.prevent="shareConversation('{{ $conversation->id }}')">Share</flux:menu.item>
                            <flux:menu.item as="button" icon="pencil" wire:click.prevent="renameConversation('{{ $conversation->id }}')">Rename</flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item as="button" variant="danger" icon="trash" wire:click.prevent="deleteConversation('{{ $conversation->id }}')">Delete</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        @empty
            <div class="px-4 py-8 text-center text-zinc-400 dark:text-zinc-500 text-sm">
                No conversations yet
            </div>
        @endforelse
    </flux:navlist>
</div>

{{-- Rename Modal --}}
<flux:modal name="rename-modal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Rename Conversation</flux:heading>
            <flux:subheading>Rename this conversation</flux:subheading>
        </div>
        <form wire:submit="saveRename">
            <flux:field>
                <flux:label>Title</flux:label>
                <flux:input wire:model="title" />
                <flux:error name="title" />
            </flux:field>
        </form>
    </div>
    <div class="flex mt-6">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="ghost">Cancel</flux:button>
        </flux:modal.close>
        <flux:button wire:click="saveRename">Save</flux:button>
    </div>
</flux:modal>

{{-- Share Modal --}}
<flux:modal name="share-modal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Share Conversation</flux:heading>
            <flux:subheading>Copy the link below to share this conversation.</flux:subheading>
        </div>
        <flux:field>
            <flux:input readonly :value="$shareUrl" />
        </flux:field>
    </div>
    <div class="flex mt-6">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="ghost">Close</flux:button>
        </flux:modal.close>
        <flux:button wire:click="copyShareLink">
            {{ $copied ? 'Copied!' : 'Copy Link' }}
        </flux:button>
    </div>
</flux:modal>

{{-- Delete Confirmation Modal --}}
<flux:modal name="delete-modal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Delete Conversation</flux:heading>
            <flux:subheading>Are you sure you want to delete this conversation? This action cannot be undone.</flux:subheading>
        </div>
    </div>
    <div class="flex mt-6">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="ghost">Cancel</flux:button>
        </flux:modal.close>
        <flux:button variant="danger" wire:click="confirmDelete">Delete</flux:button>
    </div>
</flux:modal>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    // Toast listener removed as share now uses modal
});
</script>
@endpush
