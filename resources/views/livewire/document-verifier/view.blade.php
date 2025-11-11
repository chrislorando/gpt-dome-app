<div class="space-y-4">
    @if($document)
        <div>
            <flux:heading size="lg">
                @if ($document->url)
                    <a href="{{ $document->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">{{ $document->name }}</a>
                @endif
            </flux:heading>
            <flux:subheading>Uploaded {{ $document->created_at }}</flux:subheading>
        </div>

        <div>
            <flux:field>
                <div class="flex items-center justify-between">
                    <flux:label>Verification Result</flux:label>
                    <div>
                        @php
                            $statusKey = $document->status?->value ?? ($document->status ?? 'created');
                            $status = \App\Enums\DocumentStatus::fromString($statusKey);
                        @endphp

                        <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $status->color() }}">{{ $status->label() }}</span>
                    </div>
                </div>
            </flux:field>
        </div>

         <div>
            <flux:field>
                <flux:label>Instructions</flux:label>
                <div class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">{{ $document->instructions ?? '-' }}</div>
            </flux:field>
        </div>


        <div class="shiki mt-3">
            @php
                // Attempt to decode the response as JSON and fall back to raw markdown if it's not JSON.
                // This normalizes common cases where the JSON is wrapped in a code fence (```json ... ```)
                $parsedResponse = null;
                if (!empty($document->response)) {
                    $raw = trim($document->response);

                    // Remove triple-backtick fences and optional "json" language tag
                    $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
                    $raw = preg_replace('/\s*```$/', '', $raw);

                    // Try strict decode first (throws JsonException on error)
                    try {
                        $parsedResponse = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\JsonException $e) {
                        // Try a lenient decode
                        $maybe = json_decode($raw, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $parsedResponse = $maybe;
                        } else {
                            // Sometimes APIs return a quoted JSON string, try unquoting and stripslashes
                            $unquoted = trim($raw, "\"'");
                            $unquoted = stripslashes($unquoted);
                            $maybe2 = json_decode($unquoted, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $parsedResponse = $maybe2;
                            } else {
                                $parsedResponse = null;
                            }
                        }
                    } catch (\Throwable $e) {
                        $parsedResponse = null;
                    }
                }
            @endphp

            @if (is_array($parsedResponse))
                <div class="grid grid-cols-1 gap-3">
                    @foreach ($parsedResponse as $item)
                        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-md p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-xs text-zinc-500">Page {{ $item['page'] ?? '-' }}</div>
                                    <div class="font-medium text-sm mt-1">{{ $item['section'] ?? '-' }}</div>
                                </div>

                                <div class="text-right">
                                    @php $issue = $item['issue'] ?? '' @endphp
                                    @if (strtolower(trim($issue)) === 'meets requirements' || strtolower(trim($issue)) === 'ok')
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800">Meets</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-amber-100 text-amber-800">Issue</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 text-sm text-zinc-700 dark:text-zinc-300 space-y-2">
                                <div><strong>Field:</strong> {{ $item['field'] ?? '-' }}</div>
                                <div><strong>Value:</strong> {{ $item['value'] ?? '-' }}</div>
                                <div><strong>Issue:</strong> {{ $item['issue'] ?? '-' }}</div>
                                <div><strong>Suggestion:</strong> {{ $item['suggestion'] ?? '-' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <x-markdown :anchors="false"
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
                        theme="github-dark">
                {!! $document->response !!}
            </x-markdown>
        </div>
    @else
        <div class="p-4 text-zinc-500">No document selected</div>
    @endif

    <div class="flex mt-4">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="ghost">Close</flux:button>
        </flux:modal.close>
    </div>
</div>
