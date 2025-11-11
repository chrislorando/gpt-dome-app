<?php

namespace App\Livewire\DocumentVerifier;

use App\Models\Document;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    public ?string $selectedDocumentId = null;

    public string $search = '';

    public ?string $deletingDocumentId = null;

    #[On('document-created')]
    #[On('document-deleted')]
    public function refresh(): void
    {
        // Close the form modal when a document is created (or event fired)
        // and allow the component to re-render.
        $this->modal('document-form-modal')->close();
    }

    #[On('documentUpload:open')]
    public function openForm(): void
    {
        $this->modal('document-form-modal')->show();
    }

    #[On('view-document')]
    public function viewDocument(string $documentId): void
    {
        $this->selectedDocumentId = $documentId;
        $this->modal('document-view-modal')->show();
        // Notify the browser that the view modal was opened so front-end scripts can act
        $this->dispatch('document-view-opened');
    }

    #[On('delete-document')]
    public function deleteDocument(string $documentId): void
    {
        $this->deletingDocumentId = $documentId;
        $this->modal('delete-document-modal')->show();
    }

    public function confirmDelete(): void
    {
        $document = Document::find($this->deletingDocumentId);

        if ($document) {
            // Attempt to delete the file from the configured s3 disk if we have a URL
            try {
                if ($document->url) {
                    $urlPath = parse_url($document->url, PHP_URL_PATH) ?: '';
                    $key = ltrim($urlPath, '/');

                    // If the bucket name is present in the path (path-style), strip it
                    $bucket = config('filesystems.disks.s3.bucket');
                    if ($bucket && str_starts_with($key, $bucket.'/')) {
                        $key = substr($key, strlen($bucket) + 1);
                    }

                    if ($key) {
                        Storage::disk('s3')->delete($key);
                    }
                }
            } catch (\Throwable $e) {
                // Log and continue to delete DB record
                Log::warning('Failed to delete document file from s3', ['id' => $document->id, 'error' => $e->getMessage()]);
            }

            $document->delete();
        }

        $this->modal('delete-document-modal')->close();
        $this->dispatch('document-deleted');
    }

    public function render()
    {
        $documents = Document::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('livewire.document-verifier.index', [
            'documents' => $documents,
        ]);
    }
}
