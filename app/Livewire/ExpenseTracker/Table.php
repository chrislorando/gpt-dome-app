<?php

namespace App\Livewire\ExpenseTracker;

use App\Models\Receipt;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public ?string $startDate = null;

    public ?string $endDate = null;

    // Monthly summary metrics
    public float $totalThisMonth = 0.0;

    public int $transactionsThisMonth = 0;

    public float $avgPerTransaction = 0.0;

    public ?string $topStore = null;

    public ?float $topStoreTotal = null;

    public ?int $topStoreCount = null;

    protected $listeners = [
        'refreshExpenses' => '$refresh',
        // Listen for expense lifecycle events dispatched by parent
        'expenseCreated' => 'refreshSummary',
        'expenseDeleted' => 'refreshSummary',
        'expenseUpdated' => 'refreshSummary',
    ];

    public function mount(): void
    {
        // Set default date range to current month
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->refreshSummary();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
        $this->refreshSummary();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
        $this->refreshSummary();
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

    public function refreshSummary(): void
    {
        $query = Receipt::query();

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('store_name', 'like', "%{$this->search}%")
                    ->orWhere('receipt_no', 'like', "%{$this->search}%");
            });
        }

        // Apply date range filter
        if ($this->startDate && $this->endDate) {
            try {
                $start = Carbon::parse($this->startDate)->startOfDay();
                $end = Carbon::parse($this->endDate)->endOfDay();
                $query->whereBetween('transaction_date', [$start, $end]);
            } catch (\Throwable $e) {
                // ignore parse errors
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

        $this->totalThisMonth = (float) $query->sum('total_payment');
        $this->transactionsThisMonth = (int) $query->count();
        $this->avgPerTransaction = $this->transactionsThisMonth ? ($this->totalThisMonth / $this->transactionsThisMonth) : 0.0;

        // Determine top store by total payment within filtered results
        $top = (clone $query)->selectRaw('store_name, SUM(total_payment) as total, COUNT(*) as count')
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

    public function render()
    {
        $this->refreshSummary();

        // Query for pending receipts (created and in_progress) without filters
        $pendingReceipts = Receipt::query()
            ->withCount('items')
            ->whereIn('status', ['created', 'in_progress'])
            ->latest()
            ->get();

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

        return view('livewire.expense-tracker.table', [
            'receipts' => $receipts,
            'pendingReceipts' => $pendingReceipts,
        ]);
    }
}
