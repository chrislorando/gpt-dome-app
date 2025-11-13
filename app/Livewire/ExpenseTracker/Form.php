<?php

namespace App\Livewire\ExpenseTracker;

use App\Jobs\ProcessReceipt;
use App\Models\Receipt;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class Form extends Component
{
    use WithFileUploads;

    public $file;
    public string $store_name = '';
    public string $receipt_no = '';
    public $transaction_date;

    protected $rules = [
        'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ];

    public function submit(): void
    {
        $this->validate();

        $id = (string) Str::uuid();

        $path = $this->file->store('receipts', 's3');

        $url = Storage::disk('s3')->url($path);

        $receipt = Receipt::create([
            'id' => $id,
            'user_id' => auth()->id(),
            'store_name' => '-',
            'receipt_no' => '-',
            'transaction_date' => null,
            'total_items' => 0,
            'total_discount' => 0,
            'subtotal' => 0,
            'total_payment' => 0,
            'dpp' => 0,
            'ppn' => 0,
            'file_name' => $this->file->getClientOriginalName(),
            'file_size' => $this->file->getSize(),
            'file_url' => $url,
        ]);

        // Dispatch background job to process the document
        ProcessReceipt::dispatch($receipt->id);

        $this->resetForm();

        // Notify parent components using the project's dispatch pattern
        $this->dispatch('expenseCreated');
    }

    public function resetForm(): void
    {
        $this->reset(['file', 'store_name', 'receipt_no', 'transaction_date']);
    }

    public function render()
    {
        return view('livewire.expense-tracker.form');
    }
}
