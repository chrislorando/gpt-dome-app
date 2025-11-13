<div class="rounded-md">
    <h4 class="mb-3 font-semibold text-zinc-800 dark:text-zinc-100">Upload Expense</h4>

    <form wire:submit.prevent="submit" class="space-y-4">
        <flux:field>
            <flux:label>File (jpg, png, pdf)</flux:label>
            <flux:input type="file" wire:model="file" accept="image/*,.pdf" />
            <flux:error name="file" />
        </flux:field>

        <div class="flex items-center gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Upload</flux:button>
        </div>
    </form>
</div>
