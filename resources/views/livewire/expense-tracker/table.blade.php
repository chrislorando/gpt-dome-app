<div wire:poll.15000ms>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4">
        <div class="w-full sm:flex-1">
            <flux:input class="w-full" placeholder="Search..." wire:model.live.debounce.300ms="search" />
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <flux:input class="w-full sm:w-auto" type="date" wire:model.live="startDate" value="{{ $startDate }}" />
                <span class="text-sm text-zinc-400 hidden sm:inline">—</span>
                <flux:input class="w-full sm:w-auto" type="date" wire:model.live="endDate" value="{{ $endDate }}" />
            </div>

            <div class="w-full sm:w-auto">
                <flux:button class="w-full sm:inline-flex" wire:click="openUpload">Upload</flux:button>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-sm font-medium text-zinc-400">Total Spending This Month</h3>
            <div class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ 'Rp ' . number_format($totalThisMonth, 0, ',', '.') }}</div>
        </div>

        <div class="p-4 bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-sm font-medium text-zinc-400">Number of Transactions</h3>
            <div class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $transactionsThisMonth }}</div>
        </div>

        <div class="p-4 bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-sm font-medium text-zinc-400">Average per Transaction</h3>
            <div class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ 'Rp ' . number_format($avgPerTransaction, 0, ',', '.') }}</div>
        </div>

        <div class="p-4 bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-sm font-medium text-zinc-400">Top Store / Top Category</h3>
            @if($topStore)
                <div class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $topStore }}</div>
                <div class="text-sm text-zinc-400 mt-1">{{ 'Rp ' . number_format($topStoreTotal ?? 0, 0, ',', '.') }} • {{ $topStoreCount }} transactions</div>
            @else
                <div class="text-sm text-zinc-400">No transactions this month</div>
            @endif
        </div>
    </div>

    {{-- Receipts Table Created and In Progress --}}
    @if($pendingReceipts->count() > 0)
        <div class="mb-6">
            <h3 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Processing Receipts</h3>
            <div class="overflow-x-auto">
                <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-700 overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-2 text-left">Shopping Receipt</th>
                                <th class="px-4 py-2 text-left">File Size</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Uploaded</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingReceipts as $receipt)
                                <tr class="border-t border-zinc-100 dark:border-zinc-800">
                                    <td class="px-4 py-3">
                                        <flux:link :href="$receipt->file_url" target="_blank">{{ $receipt->file_name }}</flux:link>
                                    </td>
                                    <td class="px-4 py-3">{{ $receipt->file_size }} Kb</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusKey = $receipt->status?->value ?? ($receipt->status ?? 'created');
                                            $status = \App\Enums\DocumentStatus::fromString($statusKey);
                                        @endphp

                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $status->color() }} {{ $receipt->status->value=='in_progress' ? 'animate-pulse' : '' }}">{{ $status->label() }}</span>
                                    </td>
                                    <td class="px-4 py-3">{{ $receipt->created_at->diffForHumans() }}</td>
                               
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="mb-6 rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                No receipts currently being processed
            </p>
        </div>
    @endif

    {{-- Receipts Table Completed and Failed --}}
    <div>
        <h3 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">All Receipts</h3>
        <div class="overflow-x-auto">
            <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-700 overflow-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-2 text-center">#</th>
                            <th class="px-4 py-2 text-left">Receipt No</th>
                            <th class="px-4 py-2 text-left">Store</th>
                            <th class="px-4 py-2 text-right">Items</th>
                            <th class="px-4 py-2 text-right">Total</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Uploaded</th>
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr class="border-t border-zinc-100 dark:border-zinc-800">
                                <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">{{ $receipt->receipt_no }}</td>
                                <td class="px-4 py-3">{{ $receipt->store_name }}</td>
                                <td class="px-4 py-3 text-right">{{ $receipt->items_count ?? $receipt->items->count() }}</td>
                                <td class="px-4 py-3 text-right text-nowrap">{{ $receipt->currency }} {{ number_format($receipt->total_payment, 2) }}</td>
                                <td class="px-4 py-3 text-nowrap">
                                    {{ optional($receipt->transaction_date)?->format('D, d M Y') ?? 'No date' }}
                                    <br>
                                    {{ optional($receipt->transaction_date)?->format('H:i:s') ?? '' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusKey = $receipt->status?->value ?? ($receipt->status ?? 'created');
                                        $status = \App\Enums\DocumentStatus::fromString($statusKey);
                                    @endphp

                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $status->color() }} {{ $receipt->status->value=='in_progress' ? 'animate-pulse' : '' }}">{{ $status->label() }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $receipt->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-3 text-center">
                                    <flux:button size="xs" variant="primary" :href="route('expenses.show', $receipt)" icon="eye" wire:navigate></flux:button>
                                    <flux:button size="xs" variant="danger" wire:click="confirmDelete('{{ $receipt->id }}')" icon="trash"></flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">
                                    @if($search || $startDate || $endDate)
                                        <div class="space-y-2">
                                            <p class="font-medium">No receipts found</p>
                                            <p class="text-xs">
                                                Try adjusting your search or date filters to find more results.
                                            </p>
                                        </div>
                                    @else
                                        <p>No receipts yet</p>
                                    @endif
                                </td>
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
</div>
