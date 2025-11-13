<div>
    <div class="flex items-center justify-between">
        @include('partials.general-heading', ['heading' => 'Expenses', 'subheading' => 'Uploaded receipts and expense tracking.'])
    </div>

    {{-- Summary cards placed above the table (responsive) --}}
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

    {{-- Table and controls (search, date filter, upload) placed below summary --}}
    <div class="mb-6">
        @livewire('expense-tracker.table')
    </div>

    {{-- Form modal (kept consistent with document-verifier style) --}}
    <flux:modal name="expense-form-modal" class="md:w-lg">
        <livewire:expense-tracker.form />
    </flux:modal>

    {{-- Delete confirmation modal --}}
    <flux:modal name="delete-receipt-modal" class="md:w-96">
        <div class="space-y-4">
            <flux:heading size="lg">Delete Receipt</flux:heading>
            <flux:subheading>Are you sure you want to delete this receipt? This action cannot be undone.</flux:subheading>
        </div>
        <div class="flex mt-6">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="confirmDelete">Delete</flux:button>
        </div>
    </flux:modal>

    {{-- View modal for receipt details --}}
    <flux:modal name="expense-view-modal" class="lg:w-2xl">
        <livewire:expense-tracker.detail :receipt-id="$selectedReceiptId" />
    </flux:modal>

</div>
