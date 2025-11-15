<div>
    <div class="flex items-center justify-between">
        @include('partials.general-heading', ['heading' => 'Voice Transcription', 'subheading' => 'Upload voice notes and manage transcriptions.'])
    </div>

    <div class="mb-6">
        @livewire('transcribe.table')
    </div>

    <flux:modal name="delete-voice-note-modal" class="md:w-96">
        <div class="space-y-4">
            <flux:heading size="lg">Delete Voice Note</flux:heading>
            <flux:subheading>Are you sure you want to delete this voice note? This action cannot be undone.</flux:subheading>
        </div>
        <div class="flex mt-6">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="confirmDelete">Delete</flux:button>
        </div>
    </flux:modal>

    <flux:modal name="transcribe-view-modal" class="lg:w-2xl">
        <livewire:transcribe.detail :voice-note-id="$selectedVoiceNoteId" />
    </flux:modal>
</div>
