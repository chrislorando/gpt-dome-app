<?php

namespace App\Livewire\DocumentVerifier;

use App\Models\Document;
use Livewire\Attributes\On;
use Livewire\Component;

class View extends Component
{
    public ?Document $document = null;
    // public ?string $documentId = null;

    #[On('view-document')]
    public function view($documentId ): void
    {
        $this->document = Document::find($documentId );
    }

    public function render()
    {
        return view('livewire.document-verifier.view');
    }
}
