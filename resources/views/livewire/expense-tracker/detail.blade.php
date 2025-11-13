<div class="space-y-4">
    @if ($receipt)
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Receipt number
                    </p>
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-50">
                        <flux:link :href="$receipt->file_url" target="_blank" rel="noopener noreferrer">
                            {{ $receipt->receipt_no ?: 'No transaction number' }}
                        </flux:link>
                    </h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">
                        {{ $receipt->store_name ?: 'Unknown store' }}
                    </p>
                </div>

                <div class="text-sm text-right text-zinc-600 dark:text-zinc-300">
                    <p>
                        {{ optional($receipt->transaction_date)?->format('D, d M Y') ?? 'No date' }}
                        {{ optional($receipt->transaction_date)?->format('H:i:s') ?? '' }}
                    </p>
                    <p>{{ $receipt->currency ?? '—' }}</p>
                </div>
            </div>

            <dl class="mt-6 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total items</dt>
                    <dd class="mt-1 text-base font-semibold text-zinc-900 dark:text-white">
                        {{ (int) $receipt->total_items }}
                    </dd>
                </div>

                <div class="rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Subtotal</dt>
                    <dd class="mt-1 text-base font-semibold text-zinc-900 dark:text-white">
                        {{ number_format((float) $receipt->subtotal, 2) }}
                    </dd>
                </div>

                <div class="rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Discount</dt>
                    <dd class="mt-1 text-base font-semibold text-zinc-900 dark:text-white">
                        {{ number_format((float) $receipt->total_discount, 2) }}
                    </dd>
                </div>

                <div class="rounded-lg border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-500/40 dark:bg-emerald-900/20">
                    <dt class="text-xs uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Total payment</dt>
                    <dd class="mt-1 text-lg font-semibold text-emerald-700 dark:text-emerald-200">
                        {{ number_format((float) $receipt->total_payment, 2) }}
                    </dd>
                </div>

                <div class="rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">DPP</dt>
                    <dd class="mt-1 text-base font-semibold text-zinc-900 dark:text-white">
                        {{ number_format((float) $receipt->dpp, 2) }}
                    </dd>
                </div>

                <div class="rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">PPN</dt>
                    <dd class="mt-1 text-base font-semibold text-zinc-900 dark:text-white">
                        {{ number_format((float) $receipt->ppn, 2) }}
                    </dd>
                </div>
            </dl>

            <div class="mt-8 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Items</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ $receipt->items->count() }} {{ Str::plural('item', $receipt->items->count()) }}
                    </p>
                </div>

                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right">Unit price</th>
                                <th class="px-4 py-3 text-right">Discount</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($receipt->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-zinc-800 dark:text-zinc-200">
                                        {{ $item->name ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300 text-right">
                                        {{ (int) $item->quantity }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-zinc-600 dark:text-zinc-300 text-right">
                                        {{ number_format((float) $item->unit_price, 2) }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-zinc-600 dark:text-zinc-300 text-right">
                                        {{ number_format((float) $item->discount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-zinc-800 dark:text-zinc-100 text-right">
                                        {{ number_format((float) $item->total_price, 2) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <flux:button
                                                size="xs"
                                                icon="pencil"
                                                variant="subtle"
                                                wire:click="editItem('{{ $item->id }}')"
                                            >
                                                Edit
                                            </flux:button>
                                            <flux:button
                                                size="xs"
                                                icon="trash"
                                                variant="danger"
                                                color="rose"
                                                wire:click="deleteItem('{{ $item->id }}')"
                                            >
                                                Delete
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        No line items found for this receipt.
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
                                {{ $item_id ? 'Update item' : 'Add new item' }}
                            </h5>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item_id ? 'Modify the selected line item.' : 'Capture a new line item for this receipt.' }}
                            </p>
                        </div>

                        @if ($item_id)
                            <flux:button size="xs" variant="ghost" icon="x-mark" wire:click="resetItemForm">
                                Cancel
                            </flux:button>
                        @endif
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                        <flux:field>
                            <flux:label>Item name</flux:label>
                            <flux:input wire:model.defer="item_name" placeholder="Enter item name" />
                        </flux:field>
                        
                        <flux:field>
                            <flux:label>Quantity</flux:label>
                            <flux:input wire:model.defer="item_quantity" type="number" min="1" placeholder="0" />
                        </flux:field>
                        
                        <flux:field>
                            <flux:label>Unit price</flux:label>
                            <flux:input wire:model.defer="item_unit_price" type="number" step="0.01" placeholder="0.00" />
                        </flux:field>
                        
                        <flux:field>
                            <flux:label>Discount</flux:label>
                            <flux:input wire:model.defer="item_discount" type="number" step="0.01" placeholder="0.00" />
                        </flux:field>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        @if ($item_id)
                            <flux:button size="sm" variant="primary" icon="check" color="amber" wire:click="updateItem">
                                Save changes
                            </flux:button>
                            <flux:button size="sm" variant="subtle" icon="x-mark" wire:click="resetItemForm">
                                Reset
                            </flux:button>
                        @else
                            <flux:button size="sm" variant="primary" icon="plus" wire:click="addItem">
                                Add item
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-lg border border-dashed border-zinc-300 p-6 text-center text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
            Select a receipt to view its details.
        </div>
    @endif
</div>
