<article id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 w-full min-h-28">
    <div class="w-full max-w-7xl mx-auto px-0 md:px-32">
        @foreach ($messages ?? collect() as $message)
            @if ($message->role === 'user')
                {{-- User - kanan --}}
                <div class="flex justify-end">
                    <div class="max-w-lg bg-zinc-700 text-white px-4 py-2 rounded-lg shadow break-words">
                        <pre class="whitespace-pre-wrap break-words">{{ $message->content }}</pre>
                    </div>
                </div>
                
                    {{-- Copy button for historical user message (below, outside the bubble) --}}
                    <div class="flex justify-end mt-1">
                        <span x-data="{ copied: false }" class="inline-flex">
                            <flux:button
                                icon="document-duplicate"
                                variant="ghost"
                                x-show="!copied"
                                class="size-8 text-zinc-400 p-0"
                                @click="(() => { const parent = $el.closest('.flex'); const prev = parent ? parent.previousElementSibling : null; const node = prev ? prev.querySelector('pre') : null; if (node) { navigator.clipboard.writeText(node.textContent.trim()); copied = true; setTimeout(() => copied = false, 2000); } })()"
                            />

                            <flux:button
                                icon="check"
                                variant="ghost"
                                x-show="copied"
                                class="size-8 text-green-400 p-0"
                            />
                        </span>
                    </div>
            @else
                {{-- AI - full width dalam container --}}
                <div class="px-4 text-justify overflow-hidden max-w-full text-zinc-600 dark:text-white">
                        {{-- {!! \App\Services\MarkdownParser::parse($message->content) !!} --}}
                        {{-- {!! \GrahamCampbell\Markdown\Facades\Markdown::convert($message->content)->getContent() !!} --}}
                        {{-- {!! Illuminate\Support\Str::markdown($message->content) !!} --}}
                        <div class="shiki">
                            <x-markdown
                                :anchors="false"
                                :options="[
                                    'commonmark' => [
                                        'enable_em' => true,
                                        'enable_strong' => true,
                                        'use_asterisk' => true,
                                        'use_underscore' => true,
                                        // 'unordered_list_markers' => ['-', '*', '+'],
                                    ],
                                    'html_input' => 'strip',
                                    'max_nesting_level' => 10,
                                    'renderer' => [
                                        'block_separator' => PHP_EOL,
                                        'inner_separator' => PHP_EOL,
                                        'soft_break' => PHP_EOL,
                                    ],
                                ]"
                                theme="github-dark"
                            >{!! $message->content !!}</x-markdown>
                        </div>
                    </div>
                
                @if($message->content)
                    {{-- Copy button for historical assistant message (below, outside the bubble) --}}
                    <div class="flex justify-start">
                        <span x-data="{ copied: false }" class="inline-flex">
                            <flux:button
                                icon="document-duplicate"
                                variant="ghost"
                                x-show="!copied"
                                class="size-8 text-zinc-500 dark:text-zinc-400"
                                @click="(() => { const parent = $el.closest('.flex'); const prev = parent ? parent.previousElementSibling : null; const node = prev ? (prev.querySelector('.shiki') || prev.querySelector('pre')) : null; if (node) { navigator.clipboard.writeText(node.textContent.trim()); copied = true; setTimeout(() => copied = false, 2000); } })()"
                            />

                            <flux:button
                                icon="check"
                                variant="ghost"
                                x-show="copied"
                                class="size-8 text-green-500 dark:text-green-400"
                            />

                            <flux:button
                                icon="share"
                                variant="ghost"
                                class="size-8 text-zinc-500 dark:text-zinc-400 "
                                wire:click="shareConversation({{ $message->id }})"
                            />
                        </span>
                    </div>
                @endif
            @endif
        @endforeach

        @if ($messages->isEmpty() && empty($question))
            {{-- Placeholder for empty chat --}}
            <div class="flex flex-col items-center justify-center h-64 text-center text-gray-500 dark:text-gray-400 mt-10">
                <flux:icon name="chat-bubble-left-right" class="w-16 h-16 mb-4 opacity-50" />
                <flux:heading size="lg" class="mb-2">Start a New Conversation</flux:heading>
                <p>Send your first message to start chatting with AI.</p>
            </div>
        @endif

        @if ($question)
            <div class="flex justify-end my-4">

                <div class="max-w-lg bg-zinc-700 text-white px-4 py-2 rounded-lg shadow break-words relative group">
                    <pre class="whitespace-pre-wrap break-words">{{ $question }}</pre>
                </div>

            </div>

            <div class="my-4 relative group">

                <div class=" px-4 py-2 leading-8 break-words text-justify text-zinc-600 dark:text-white">
                    <div wire:stream="answer">
                        {!! \App\Services\MarkdownParser::parse($answer) !!}
                    </div>
                </div>

            </div>
        @endif
    </div>

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

</article>

@script
<script>
let intervalId; 

document.addEventListener('livewire:navigated', () => {
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});

$wire.on('scroll-down', (event) => {
    const container = document.getElementById('chat-messages');
    let autoScroll = true;

    container.addEventListener('scroll', () => {
        const nearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 80;
        // console.log('nearBottom:', nearBottom);
        
        if (!nearBottom) {
            // User scroll ke atas, stop auto scroll
            autoScroll = false;
            clearInterval(intervalId);
            console.log('Auto scroll stopped');
        } else if (!autoScroll) {
            // User scroll kembali ke bawah, start auto scroll
            autoScroll = true;
            intervalId = setInterval(function() {
                if (autoScroll && container) {
                    container.scrollTop = container.scrollHeight;
                    // console.log('Auto scrolling...');
                }
            }, 1000);
            console.log('Auto scroll started');
        }
    });

    // Start initial auto scroll
    intervalId = setInterval(function() {
        if (autoScroll && container) {
            container.scrollTop = container.scrollHeight;
            console.log('Initial auto scrolling...');
        }
    }, 1000);
});

$wire.on('scroll-stop', (event) => {
    console.log('STOP');
    clearInterval(intervalId);
})
</script>
@endscript