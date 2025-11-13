<?php

namespace App\Livewire\ExpenseTracker;

use App\Models\Receipt as ReceiptModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public bool $showForm = false;

    // ID of the receipt pending deletion
    public ?string $deletingReceiptId = null;

    public ?string $selectedReceiptId = null;

    #[On('expenseUpload:open')]
    public function openForm(): void
    {
        // Use the modal helper (same pattern as DocumentVerifier) to show the modal
        $this->modal('expense-form-modal')->show();
    }

    public function closeForm(): void
    {
        $this->modal('expense-form-modal')->close();
    }

    public function render()
    {
        return view('livewire.expense-tracker.index');
    }

    #[On('expenseCreated')]
    public function handleExpenseCreated(): void
    {
        $this->closeForm();
    }

    #[On('delete-receipt')]
    public function deleteReceipt(string $receiptId): void
    {
        $this->deletingReceiptId = $receiptId;
        $this->modal('delete-receipt-modal')->show();
    }

    #[On('showExpenseDetail')]
    public function viewReceipt(string $receiptId): void
    {
        $this->selectedReceiptId = $receiptId;
        $this->modal('expense-view-modal')->show();
    }

    public function confirmDelete(): void
    {
        $receipt = ReceiptModel::find($this->deletingReceiptId);

        if ($receipt) {
            try {
                // If the raw response contains a stored file path, attempt to delete it from the public disk
                $file = data_get($receipt->response, 'file');
                if ($file && Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to delete receipt file', ['id' => $receipt->id, 'error' => $e->getMessage()]);
            }

            // Delete any receipt items first then the receipt record
            try {
                $receipt->items()->delete();
            } catch (\Throwable $e) {
                Log::warning('Failed to delete receipt items', ['id' => $receipt->id, 'error' => $e->getMessage()]);
            }

            $receipt->delete();
        }

        $this->modal('delete-receipt-modal')->close();
        // Notify table components to refresh
        $this->dispatch('expenseDeleted');
    }
}
