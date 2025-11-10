<div>
    <div class="space-y-4">
        <div>
            <flux:heading size="lg">Upload PDF</flux:heading>
            <flux:subheading>Upload a PDF and provide instructions for verification</flux:subheading>
        </div>

        <form wire:submit.prevent="submit" class="space-y-4">
            <flux:field>
                <flux:label>Instructions (optional)</flux:label>
                <flux:textarea wire:model="instructions" rows="4" />
                <flux:error name="instructions" />
            </flux:field>

            <flux:field>
                <flux:label>PDF File</flux:label>
                <flux:input type="file" wire:model="file" accept="application/pdf" />
                <flux:error name="file" />
            </flux:field>
        
            <div class="flex items-center gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit">Upload</flux:button>
            </div>
        </form>
    </div>
</div>
