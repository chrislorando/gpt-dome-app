<div wire:poll.15000ms>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4">
        <div class="w-full sm:flex-1">
            <flux:input class="w-full" placeholder="Search..." wire:model.live.debounce.300ms="search" />
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <flux:input class="w-full sm:w-auto" type="date" wire:model.lazy="startDate" />
                <span class="text-sm text-zinc-400 hidden sm:inline">—</span>
                <flux:input class="w-full sm:w-auto" type="date" wire:model.lazy="endDate" />
            </div>

            <div class="w-full sm:w-auto">
                <flux:button class="w-full sm:inline-flex" wire:click="openUpload">Upload</flux:button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-700 overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-2 text-left">Receipt No</th>
                        <th class="px-4 py-2 text-left">Store</th>
                        <th class="px-4 py-2 text-left">Items</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Uploaded</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                        <tr class="border-t border-zinc-100 dark:border-zinc-800">
                            <td class="px-4 py-3">{{ $receipt->receipt_no }}</td>
                            <td class="px-4 py-3">{{ $receipt->store_name }}</td>
                            <td class="px-4 py-3">{{ $receipt->items_count ?? $receipt->items->count() }}</td>
                            <td class="px-4 py-3 text-right">{{ $receipt->currency }} {{ number_format($receipt->total_payment, 2) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusKey = $receipt->status?->value ?? ($receipt->status ?? 'created');
                                    $status = \App\Enums\DocumentStatus::fromString($statusKey);
                                @endphp

                                <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $status->color() }} {{ $receipt->status->value=='in_progress' ? 'animate-pulse' : '' }}">{{ $status->label() }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $receipt->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <flux:button size="sm" variant="primary" wire:click="showDetail('{{ $receipt->id }}')" icon="eye"></flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="confirmDelete('{{ $receipt->id }}')" icon="trash"></flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                                <td colspan="10" class="px-4 py-6 text-center text-zinc-400">No receipts yet</td>
                            </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $receipts->links('pagination::tailwind') }}
    </div>
</div>
