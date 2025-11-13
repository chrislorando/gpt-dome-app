<?php

namespace App\Livewire\ExpenseTracker;

use App\Models\Receipt as ReceiptModel;
use Livewire\Component;
use Livewire\Attributes\On;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    public bool $showForm = false;

    // Events are handled via attributes (Livewire 3 conventions)

    public int $totalReceipts = 0;
    public int $totalItems = 0;
    // Monthly summary metrics
    public float $totalThisMonth = 0.0;
    public int $transactionsThisMonth = 0;
    public float $avgPerTransaction = 0.0;
    public ?string $topStore = null;
    public ?float $topStoreTotal = null;
    public ?int $topStoreCount = null;

    public function mount(): void
    {
        $this->refreshSummary();
    }

    // ID of the receipt pending deletion
    public ?string $deletingReceiptId = null;
    public ?string $selectedReceiptId = null;

    #[On('expenseUpdated')]
    public function refreshSummary(): void
    {
        $this->totalReceipts = ReceiptModel::count();
        $this->totalItems = ReceiptModel::sum('total_items');

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $monthQuery = ReceiptModel::query()->whereBetween('transaction_date', [$start, $end]);

        $this->totalThisMonth = (float) $monthQuery->sum('total_payment');
        $this->transactionsThisMonth = (int) $monthQuery->count();
        $this->avgPerTransaction = $this->transactionsThisMonth ? ($this->totalThisMonth / $this->transactionsThisMonth) : 0.0;

        // Determine top store by total payment within the month (fallback to most transactions if tied)
        $top = $monthQuery->selectRaw('store_name, SUM(total_payment) as total, COUNT(*) as count')
            ->groupBy('store_name')
            ->orderByDesc('total')
            ->orderByDesc('count')
            ->first();

        if ($top) {
            $this->topStore = $top->store_name;
            $this->topStoreTotal = (float) $top->total;
            $this->topStoreCount = (int) $top->count;
        } else {
            $this->topStore = null;
            $this->topStoreTotal = null;
            $this->topStoreCount = null;
        }
    }

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
        $this->refreshSummary();
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
        $this->refreshSummary();
        // Notify table components to refresh
        $this->dispatch('expenseDeleted');
    }
}
