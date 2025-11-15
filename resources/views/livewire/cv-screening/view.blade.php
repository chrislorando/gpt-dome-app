<div class="space-y-4" wire:poll.15000ms>
    @if ($cv)
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">CV File</p>
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-50">
                        @if ($cv->file_url)
                            <a href="{{ $cv->file_url }}" target="_blank" rel="noopener noreferrer" class="underline decoration-blue-500 text-blue-600 dark:text-blue-400">{{ $cv->file_name }}</a>
                        @else
                            {{ $cv->file_name }}
                        @endif
                    </h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">Uploaded {{ $cv->created_at }} • Model {{ $cv->model_id }}</p>
                </div>
                <div class="text-sm text-right text-zinc-600 dark:text-zinc-300">
                    @php
                        $statusKey = $cv->status?->value ?? ($cv->status ?? 'created');
                        $status = \App\Enums\DocumentStatus::fromString($statusKey);
                    @endphp
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $status->color() }} {{ $cv->status->value==='in_progress' ? 'animate-pulse' : '' }}">{{ $status->label() }}</span>
                </div>
            </div>

            @if($cv->job_position)
                <div class="mt-6 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-2">Job Position</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white whitespace-pre-wrap">{{ $cv->job_position }}</dd>
                </div>
            @endif

            <div class="mt-4 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-2">Job Offer</dt>
                @php
                    $rawOffer = trim((string)($cv->job_offer ?? ''));
                    $paragraphs = preg_split('/\r?\n\s*\r?\n/', $rawOffer);
                    $paragraphs = $paragraphs === false ? [] : array_filter(array_map('trim', $paragraphs));
                    $visibleCount = 3;
                @endphp
                @if(empty($paragraphs))
                    <dd class="text-sm text-zinc-900 dark:text-white">-</dd>
                @else
                    <div class="space-y-3 text-sm text-zinc-800 dark:text-zinc-200">
                        @foreach(array_slice($paragraphs,0,$visibleCount) as $p)
                            <p class="whitespace-pre-wrap">{{ $p }}</p>
                        @endforeach
                        @if(count($paragraphs) > $visibleCount)
                            <details class="mt-1">
                                <summary class="cursor-pointer text-xs text-blue-600 dark:text-blue-400">Show {{ count($paragraphs) - $visibleCount }} more</summary>
                                <div class="mt-2 space-y-3">
                                    @foreach(array_slice($paragraphs,$visibleCount) as $p)
                                        <p class="whitespace-pre-wrap">{{ $p }}</p>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>
                @endif
            </div>

            <div class="mt-4 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-3">Scores</dt>
                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-3 text-right">Skill</th>
                                <th class="px-4 py-3 text-right">Experience</th>
                                <th class="px-4 py-3 text-right">Education</th>
                                <th class="px-4 py-3 text-right">Overall</th>
                                <th class="px-4 py-3 text-center">Recommended</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            <tr class="bg-zinc-50 dark:bg-zinc-800">
                                <td class="px-4 py-3 text-right">{{ number_format($cv->skill_match,0,'','') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($cv->experience_match,0,'','') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($cv->education_match,0,'','') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($cv->overall_score,0,'','') }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($cv->is_recommended)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Yes</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-zinc-100 text-zinc-800 dark:bg-zinc-900 dark:text-zinc-200">No</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if($cv->suggestion)
                <div class="mt-4 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-2">Suggestion</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white whitespace-pre-wrap">{{ $cv->suggestion }}</dd>
                </div>
            @endif

            @if($cv->summary)
                <div class="mt-4 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-2">Summary</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white whitespace-pre-wrap">{{ $cv->summary }}</dd>
                </div>
            @endif

            @if($cv->cover_letter)
                <div class="mt-4 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-2">Cover Letter</dt>
                    <dd class="prose dark:prose-invert max-w-none text-sm">{!! $cv->cover_letter !!}</dd>
                </div>
            @endif
        </div>
    @else
        <div class="rounded-lg border border-dashed border-zinc-300 p-6 text-center text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
            No CV selected.
        </div>
    @endif
</div>
