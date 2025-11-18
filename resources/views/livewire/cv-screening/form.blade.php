<div class="w-full">
    <div class="space-y-4 max-w-3xl">
        <div>
            <flux:heading size="lg">Upload CV</flux:heading>
            <flux:subheading>Upload a PDF of the CV</flux:subheading>
        </div>

        <form wire:submit.prevent="submit" class="space-y-4">
            <flux:field>
                <flux:label>Model</flux:label>
                <flux:select wire:model="model_id">
                    <option value="">Use default model</option>
                    @foreach($models as $m)
                        <option value="{{ $m->id }}">{{ $m->id }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="model_id" />
            </flux:field>

            <flux:field>
                <flux:label>Job Offer Description</flux:label>
                <flux:textarea wire:model="job_offer" rows="4" placeholder="Fullstack engineer position at a tech company..." />
                {{-- <flux:error name="job_offer" /> --}}
            </flux:field>

            <div
                x-data="{ uploading: false, progress: 0 }"
                x-on:livewire-upload-start="uploading = true"
                x-on:livewire-upload-finish="uploading = false"
                x-on:livewire-upload-cancel="uploading = false"
                x-on:livewire-upload-error="uploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
            >
                <flux:field>
                    <flux:label>PDF File</flux:label>
                    <flux:input type="file" wire:model="file" accept="application/pdf" class="max-w-full truncate" />
                    <flux:error name="file" />
                </flux:field>

                <div x-show="uploading" class="flex items-center gap-2 mt-2">
                    <progress max="100" x-bind:value="progress" class="w-full h-2"></progress>
                    <flux:button type="button" variant="danger" wire:click="$cancelUpload('file')" size="sm">
                        <flux:icon name="trash" class="w-4 h-4" />
                    </flux:button>
                </div>
            </div>
        
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
