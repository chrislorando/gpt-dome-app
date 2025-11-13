<div>
    <div class="flex items-center justify-between">
        @include('partials.general-heading', ['heading' => 'Expenses', 'subheading' => 'Uploaded receipts and expense tracking.'])
    </div>

    {{-- Table with summary and controls --}}
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
