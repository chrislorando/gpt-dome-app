<div>
    @if($receipt)
        <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-700 p-4">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Receipt {{ $receipt->receipt_no }}</h3>
            <div class="text-sm text-zinc-600 dark:text-zinc-300">Store: {{ $receipt->store_name }}</div>

            <div class="mt-4">
                <h4 class="font-medium text-zinc-800 dark:text-zinc-100">Items</h4>
                <div class="overflow-x-auto mt-2">
                    <table class="min-w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Qty</th>
                                <th class="px-4 py-2 text-left">Unit</th>
                                <th class="px-4 py-2 text-left">Total</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receipt->items as $item)
                                <tr class="border-t border-zinc-100 dark:border-zinc-800">
                                    <td class="px-4 py-3">{{ $item->name }}</td>
                                    <td class="px-4 py-3">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-4 py-3">{{ number_format($item->total_price, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <flux:button size="sm" variant="primary" wire:click="editItem({{ $item->id }})" icon="pencil">Edit</flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <h5 class="font-medium text-zinc-800 dark:text-zinc-100">Add / Edit Item</h5>
                    <div class="grid grid-cols-4 gap-2 mt-2">
                        <flux:input wire:model="item_name" placeholder="Name" class="col-span-2" />
                        <flux:input wire:model="item_quantity" type="number" min="1" />
                        <flux:input wire:model="item_unit_price" type="number" step="0.01" />
                    </div>
                    <div class="mt-2">
                        <flux:input wire:model="item_discount" type="number" step="0.01" placeholder="Discount" />
                    </div>

                    <div class="mt-3">
                        @if($item_id)
                            <flux:button size="sm" variant="warning" wire:click="updateItem">Update Item</flux:button>
                            <flux:button size="sm" variant="muted" wire:click="resetItemForm">Cancel</flux:button>
                        @else
                            <flux:button size="sm" variant="primary" wire:click="addItem">Add Item</flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-zinc-400">Select a receipt to see details.</div>
    @endif
    
</div>
