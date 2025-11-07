<article id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 w-full min-h-28">
    <div class="w-full max-w-7xl mx-auto px-0 md:px-16">
        @foreach ($messages ?? collect() as $message)
            @if ($message->role === 'user')
                {{-- User - kanan --}}
                <div class="flex justify-end my-4">
                    <div class="max-w-lg bg-zinc-700 text-white px-4 py-2 rounded-lg shadow break-words">
                        <pre class="whitespace-pre-wrap break-words">{{ $message->content }}</pre>
                    </div>

                </div>
            @else
                {{-- AI - full width dalam container --}}
                <div class="my-4">
                    <div class="px-4 py-2 rounded-lg shadow  text-justify overflow-hidden max-w-full">
                        {{-- {!! \App\Services\MarkdownParser::parse($message->content) !!} --}}
                        {{-- {!! \GrahamCampbell\Markdown\Facades\Markdown::convert($message->content)->getContent() !!} --}}
                        {{-- {!! Illuminate\Support\Str::markdown($message->content) !!} --}}
                        <div class="break-words leading-8 overflow-wrap-anywhere [&_*]:max-w-full [&_pre]:overflow-x-auto [&_pre]:whitespace-pre-wrap [&_code]:break-all [&_table]:block [&_table]:overflow-x-auto">
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
                                    'html_input' => 'escape',
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
                </div>
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

                <div class="max-w-lg bg-zinc-700 text-white px-4 py-2 rounded-lg shadow break-words">
                    <pre class="whitespace-pre-wrap break-words">{{ $question }}</pre>
                </div>

            </div>

            <div class="my-4">
                <div class=" px-4 py-2 rounded-lg shadow leading-8 break-words text-justify">
                    <div wire:stream="answer">
                        {!! \App\Services\MarkdownParser::parse($answer) !!}
                    </div>
                </div>
            </div>
        @endif
    </div>
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