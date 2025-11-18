<?php

namespace App\Livewire\DocumentVerifier;

use App\Jobs\ProcessDocument;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public $instructions;
    public $file;

    protected $rules = [
        'instructions' => 'nullable|string|max:1000',
        'file' => 'required|file|mimes:pdf|max:5120',
    ];

    public function submit(): void
    {
        $this->validate();

        $path = $this->file->store('documents', 's3');
        $url = Storage::disk('s3')->url($path);

        $document = Document::create([
            'name' => $this->file->getClientOriginalName(),
            'size' => $this->file->getSize(),
            'instructions' => $this->instructions,
            'url' => $url,
            'user_id' => auth()->id(),
            // status defaults to created in DB migration
        ]);

        // Dispatch background job to process the document
        ProcessDocument::dispatch($document->id);

        // Close modal first to avoid re-render hiding the modal before it closes
        $this->modal('document-form-modal')->close();

        // Reset local state
        $this->reset(['instructions', 'file']);

        // Notify parent/listeners that a document was created
        $this->dispatch('document-created');
    }

    public function render()
    {
        return view('livewire.document-verifier.form');
    }
}
