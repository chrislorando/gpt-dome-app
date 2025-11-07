<div class="flex flex-col h-screen bg-zinc-50 dark:bg-zinc-900" wire:key="chat-bot-ai-{{ $conversationId }}">
    <livewire:chat.chat-bot-ai-response :conversationId="$conversationId" wire:key="chat-bot-ai-content-{{ $conversationId }}" />
    
    <form wire:submit.prevent="submitPrompt" class="mt-14 sticky bottom-0  p-4 border-t border-gray-200 dark:border-zinc-700">
        <div class="w-full mx-auto px-0 md:px-16">
            <div class="flex flex-col md:flex-row items-stretch md:items-end gap-2">
                
                <!-- Desktop left select (hidden on mobile) -->
                <div class="hidden md:block flex-shrink-0 order-2 md:order-1 w-auto md:w-auto mt-0 md:mt-0">
                    <flux:select
                        wire:model.live="selectedModel"
                        class="px-4 py-2 pr-8 border border-gray-300 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-zinc-800 dark:text-white"
                    >
                        @foreach($activeModels as $model)
                            <flux:select.option>{{ $model->id }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <!-- Middle block (textarea) -->
                <div class="flex-1 w-full flex items-end order-1 md:order-2">
                    <flux:textarea
                        id="prompt"
                        wire:model="prompt"
                        placeholder="How can I help you today?"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-zinc-800 dark:text-white resize-none max-h-48 overflow-y-auto"
                        rows="auto"
                        resize="none"
                    />
                </div>

                <!-- Mobile controls: select + button in the same row (visible on small screens only) -->
                <div class="md:hidden order-2 w-full mt-2 flex items-end gap-2">
                    <flux:select
                        wire:model.live="selectedModel"
                        class="flex-1 px-4 py-2 pr-8 border border-gray-300 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-zinc-800 dark:text-white"
                    >
                        @foreach($activeModels as $model)
                            <flux:select.option>{{ $model->id }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <div class="flex-shrink-0">
                        @if($question)
                            <flux:button wire:click="cancel" variant="primary">
                                <flux:icon name="stop-circle" class="animate-pulse text-red-500" />
                            </flux:button>
                        @else
                            <flux:button type="submit" variant="primary" wire:disabled="$question">
                                <flux:icon name="paper-airplane" />
                            </flux:button>
                        @endif
                    </div>
                </div>

                <!-- Desktop right button (hidden on mobile) -->
                <div class="hidden md:flex flex-shrink-0 order-3 md:order-3 w-auto md:w-auto mt-0 md:mt-0 justify-start">
                    @if($question)
                        <flux:button wire:click="cancel" variant="primary">
                            <flux:icon name="stop-circle" class="animate-pulse text-red-500" />
                        </flux:button>
                    @else
                        <flux:button type="submit" variant="primary" wire:disabled="$question">
                            <flux:icon name="paper-airplane" />
                        </flux:button>
                    @endif
                </div>

            </div>
        </div>
    </form>

</div>

@script
<script>

$wire.on('chat-navigated', (event) => {
    const promptTextarea = document.getElementById('prompt');
    if (!promptTextarea) return;

    promptTextarea.focus();

    promptTextarea.onkeydown = function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $wire.call('submitPrompt');
        }
    };

    const shouldSubmit = @js($submitOnNavigate);
    if (shouldSubmit) {
        setTimeout(() => $wire.call('submitPrompt'), 50);
    }
})

let currentController = null;

Livewire.hook('request', ({ options }) => {
    currentController = new AbortController();
    options.signal = currentController.signal;
});

$wire.on('stream-stop', (event) => {
    if (currentController) {
        currentController.abort();
        console.log('ABORTED');
    }
})


</script>
@endscript