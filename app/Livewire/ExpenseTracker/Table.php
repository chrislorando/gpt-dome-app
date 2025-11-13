<?php

namespace App\Livewire\ExpenseTracker;

use App\Models\Receipt;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Table extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public ?string $startDate = null;
    public ?string $endDate = null;

    protected $listeners = [
        'refreshExpenses' => '$refresh',
        // Listen for expense lifecycle events dispatched by parent
        'expenseCreated' => '$refresh',
        'expenseDeleted' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    public function showDetail(string $id): void
    {
        // Use the project's dispatch pattern so parent and other components
        // can react via attribute listeners (Livewire v3 style).
        $this->dispatch('showExpenseDetail', receiptId: $id);
    }

    public function confirmDelete(string $id): void
    {
        // Dispatch to parent to show the delete confirmation modal
        $this->dispatch('delete-receipt', receiptId: $id);
    }

    public function openUpload(): void
    {
        $this->dispatch('expenseUpload:open');
    }

    public function render()
    {
        $query = Receipt::query()->withCount('items')->latest();

        if ($this->search) {
            $query->where('store_name', 'like', "%{$this->search}%")
                ->orWhere('receipt_no', 'like', "%{$this->search}%");
        }

        // Apply transaction date range filter when provided
        if ($this->startDate && $this->endDate) {
            try {
                $start = Carbon::parse($this->startDate)->startOfDay();
                $end = Carbon::parse($this->endDate)->endOfDay();
                $query->whereBetween('transaction_date', [$start, $end]);
            } catch (\Throwable $e) {
                // ignore parse errors and do not apply date filter
            }
        } elseif ($this->startDate) {
            try {
                $start = Carbon::parse($this->startDate)->startOfDay();
                $query->where('transaction_date', '>=', $start);
            } catch (\Throwable $e) {
                // ignore
            }
        } elseif ($this->endDate) {
            try {
                $end = Carbon::parse($this->endDate)->endOfDay();
                $query->where('transaction_date', '<=', $end);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $receipts = $query->paginate($this->perPage);

        return view('livewire.expense-tracker.table', ['receipts' => $receipts]);
    }
}
