<div class="flex flex-col h-screen bg-zinc-50 dark:bg-zinc-900 pb-16">
    <article id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 w-full min-h-28">
        <div class="w-full max-w-7xl mx-auto px-0 md:px-32">

            @if ($title)
                <div class="mb-6">
                    <p class="text-sm text-center font-semibold text-zinc-800 dark:text-white">
                        {{-- {{ $title }} --}}
                        Content created using Demolite.
                    </p>
                </div>
            @endif

            @foreach ($contents ?? [] as $content)
                @if (data_get($content, 'role') === 'user')
                    <div class="flex justify-end mb-2">
                        <div class="max-w-lg bg-zinc-700 text-white px-4 py-2 rounded-lg shadow break-words">
                            <pre class="whitespace-pre-wrap break-words">{{ data_get($content, 'content') }}</pre>
                        </div>
                    </div>

                @else
                    <div class="mb-2 px-4 py-2 text-justify overflow-hidden max-w-full text-zinc-600 dark:text-white">
                        <div class="shiki">
                            <x-markdown
                                :anchors="false"
                                :options="[
                                    'commonmark' => [
                                        'enable_em' => true,
                                        'enable_strong' => true,
                                        'use_asterisk' => true,
                                        'use_underscore' => true,
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
                            >{!! data_get($content, 'content') !!}</x-markdown>
                        </div>
                    </div>

                @endif
            @endforeach

            @if (empty($contents))
                <div class="flex flex-col items-center justify-center h-64 text-center text-gray-500 dark:text-gray-400 mt-10">
                    <flux:icon name="chat-bubble-left-right" class="w-16 h-16 mb-4 opacity-50" />
                    <flux:heading size="lg" class="mb-2">Conversation Not Found</flux:heading>
                    <p>This shared conversation doesn’t have any messages.</p>
                </div>
            @endif

        </div>
    </article>
</div>