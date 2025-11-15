<div class="space-y-4">
    @if ($voiceNote)
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Voice Note</p>
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-50">
                        {{ $voiceNote->title }}
                    </h3>
                     <flux:link :href="$voiceNote->file_url" target="_blank" rel="noopener noreferrer">
                        {{ $voiceNote->file_name }}
                    </flux:link>
                   
                </div>

                <div class="text-sm text-right text-zinc-600 dark:text-zinc-300">
                    <p>{{ $voiceNote->created_at }}</p>
                    <p>Duration: {{ $voiceNote->duration ?? 0 }}s</p>
                </div>
            </div>

            <div class="mt-4 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-2">Preview</dt>
                <audio controls preload="metadata" class="w-full" src="{{ $voiceNote->file_url }}"></audio>
            </div>

            @if($voiceNote->transcript)
                <div class="mt-6 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-2">Transcript</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white whitespace-pre-wrap">{{ $voiceNote->transcript }}</dd>
                </div>
            @endif

            @if($voiceNote->response)
                <div class="mt-4 rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-2">AI Response</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white whitespace-pre-wrap">{{ $voiceNote->response }}</dd>
                </div>
            @endif

            @if($voiceNote->tags && count($voiceNote->tags) > 0)
                <div class="mt-4">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-2">Tags</dt>
                    <div class="flex flex-wrap gap-2">
                        @foreach($voiceNote->tags as $tag)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-8 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Action Items</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ $voiceNote->items->count() }} {{ Str::plural('item', $voiceNote->items->count()) }}
                    </p>
                </div>

                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Description</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Due Date</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($voiceNote->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-zinc-800 dark:text-zinc-200">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->status->value === 'done')
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Done</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Todo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-zinc-600 dark:text-zinc-300">
                                        {{ $item->due_date?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <flux:button size="xs" icon="pencil" variant="primary" wire:click="editItem('{{ $item->id }}')"></flux:button>
                                            <flux:button size="xs" icon="trash" variant="danger" wire:click="deleteItem('{{ $item->id }}')"></flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        No action items found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                

                <div class="rounded-lg border border-dashed border-zinc-300 p-4 dark:border-zinc-700">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h5 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $item_id ? 'Update action item' : 'Add new action item' }}
                            </h5>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item_id ? 'Modify the selected action item.' : 'Create a new action item for this voice note.' }}
                            </p>
                        </div>

                        @if ($item_id)
                            <flux:button size="xs" variant="ghost" icon="x-mark" wire:click="resetItemForm">Cancel</flux:button>
                        @endif
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <flux:field>
                            <flux:label>Description</flux:label>
                            <flux:textarea wire:model.defer="item_description" placeholder="Enter description" rows="1" />
                        </flux:field>
                        
                        <flux:field>
                            <flux:label>Status</flux:label>
                            <flux:select wire:model.defer="item_status">
                                <option value="todo">Todo</option>
                                <option value="done">Done</option>
                            </flux:select>
                        </flux:field>
                        
                        <flux:field>
                            <flux:label>Due Date</flux:label>
                            <flux:input wire:model.defer="item_due_date" type="date" />
                        </flux:field>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        @if ($item_id)
                            <flux:button size="sm" variant="primary" icon="check" wire:click="updateItem">Save changes</flux:button>
                            <flux:button size="sm" variant="subtle" icon="x-mark" wire:click="resetItemForm">Reset</flux:button>
                        @else
                            <flux:button size="sm" variant="primary" icon="plus" wire:click="addItem">Add item</flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-lg border border-dashed border-zinc-300 p-6 text-center text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
            Select a voice note to view its details.
        </div>
    @endif
</div>
