<?php

namespace App\Livewire\DocumentVerifier;

use App\Models\Document;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    public string $search = '';

    protected $updatesQueryString = ['search'];

    protected $listeners = [
        'document-created' => '$refresh',
        'document-deleted' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function view(string $id): void
    {
        $this->dispatch('view-document', documentId: $id);
    }

    public function openUpload(): void
    {
        // Dispatch an event up to the parent Livewire component to open the upload modal.
        $this->dispatch('documentUpload:open');
    }

    public function confirmDelete(string $id): void
    {
        $this->dispatch('delete-document', documentId: $id);
    }

    public function render()
    {
        $documents = Document::query()
            ->where('user_id', auth()->id())
            ->when($this->search, function($q){
                $q->whereLike('name',  '%'.$this->search.'%')
                ->orWhereLike('instructions',  '%'.$this->search.'%')
                ->orWhereLike('response',  '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.document-verifier.table', [
            'documents' => $documents,
        ]);
    }
}
