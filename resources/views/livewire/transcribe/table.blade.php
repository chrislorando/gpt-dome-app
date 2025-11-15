<div wire:poll.15000ms>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4">
        <div class="flex items-center gap-2">
            <flux:input placeholder="Search..." wire:model.live.debounce.300ms="search" />
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            <div class="w-full sm:w-auto">
                <flux:button class="w-full sm:inline-flex" wire:click="openUpload">Create</flux:button>
            </div>
        </div>
    </div>


    <div>
        <div class="overflow-x-auto">
            <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-700 overflow-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-2 text-center">#</th>
                            <th class="px-4 py-2 text-left">Title</th>
                            <th class="px-4 py-2 text-left">File Name</th>
                            <th class="px-4 py-2 text-right">Items</th>
                            <th class="px-4 py-2 text-right">Duration</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Uploaded</th>
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($voiceNotes as $voiceNote)
                            <tr class="border-t border-zinc-100 dark:border-zinc-800">
                                <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">{{ $voiceNote->title }}</td>
                                <td class="px-4 py-3">
                                    <flux:link :href="$voiceNote->file_url" target="_blank">{{ $voiceNote->file_name }}</flux:link>
                                </td>
                                <td class="px-4 py-3 text-right">{{ $voiceNote->items_count ?? $voiceNote->items->count() }}</td>
                                <td class="px-4 py-3 text-right">{{ $voiceNote->duration ?? 0 }}s</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusKey = $voiceNote->status?->value ?? ($voiceNote->status ?? 'created');
                                        $status = \App\Enums\DocumentStatus::fromString($statusKey);
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $status->color() }} {{ $voiceNote->status->value=='in_progress' ? 'animate-pulse' : '' }}">{{ $status->label() }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $voiceNote->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-3 text-center">
                                    <flux:button size="xs" variant="primary" :href="route('transcribe.show', $voiceNote)" icon="eye" wire:navigate></flux:button>
                                    <flux:button size="xs" variant="danger" wire:click="confirmDelete('{{ $voiceNote->id }}')" icon="trash"></flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">
                                    @if($search || $startDate || $endDate)
                                        <div class="space-y-2">
                                            <p class="font-medium">No voice notes found</p>
                                        </div>
                                    @else
                                        <p>No voice notes yet</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $voiceNotes->links('pagination::tailwind') }}
        </div>
    </div>
</div>
