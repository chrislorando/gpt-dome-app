<div>
    <div class="flex items-center justify-between">
        @include('partials.general-heading', ['heading' => 'Documents', 'subheading' => 'Uploaded PDF documents and verification results.'])
    </div>

    <livewire:document-verifier.table />

    {{-- Form modal (child component inside modal) --}}
    <flux:modal name="document-form-modal" class="md:w-96">
        <livewire:document-verifier.form />
    </flux:modal>

    {{-- View modal (child component shows details) --}}
    <flux:modal name="document-view-modal" class="lg:w-2xl">
        <livewire:document-verifier.view :document-id="$selectedDocumentId" />
    </flux:modal>

    {{-- Delete confirmation modal --}}
    <flux:modal name="delete-document-modal" class="md:w-96">
        <div class="space-y-4">
            <flux:heading size="lg">Delete Document</flux:heading>
            <flux:subheading>Are you sure you want to delete this document? This cannot be undone.</flux:subheading>
        </div>
        <div class="flex mt-6">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="confirmDelete">Delete</flux:button>
        </div>
    </flux:modal>
</div>



