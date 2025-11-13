<?php

namespace App\Jobs;

use App\Enums\ResponseStatus;
use App\Models\Document;
use App\Models\Receipt;
use App\Services\AiServiceInterface;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $documentId;
    /**
     * Number of attempts. Set to null to allow retrying until retryUntil() expires.
     * We'll use retryUntil() to control how long we want to keep retrying.
     */
    public ?int $tries = null;

    /**
     * The number of seconds to wait before retrying the job.
     * Can be a single int or an array of backoff seconds per attempt.
     */
    public int|array $backoff = [5, 10, 15];

    /**
     * Keep retrying until this timestamp. Return a DateTimeInterface in the future.
     * Set this to a sensible limit (e.g., 7 days) to avoid indefinite retry loops.
     */
    public function retryUntil(): \DateTimeInterface
    {
        return now()->addDays(7);
    }

    /**
     * Create a new job instance.
     */
    public function __construct(string $documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * Execute the job.
     */
    public function handle(AiServiceInterface $aiService): void
    {
        $document = Receipt::find($this->documentId);

        if (! $document) {
            Log::warning('Process Receipt: document not found', ['id' => $this->documentId]);
            return;
        }

        // mark as in progress
        $document->update(['status' => ResponseStatus::InProgress]);

        $response = $aiService->createReceiptResponse($document->file_url);

        // attempt to parse AI response and persist to receipts + receipt_items
        try {
            $payload = json_decode($response->outputText, true);

            // store raw response on document for traceability
            $document->update(['response' => $payload]);

            if (is_array($payload) && isset($payload['transaction'])) {
                DB::transaction(function () use ($payload, $document) {
                    $txn = $payload['transaction'];

                    // determine receipt attributes
                    $receiptNo = $txn['receipt_no'] ?? null;
                    $transactionDate = null;
                    if (! empty($txn['date'])) {
                        // try parse as datetime
                        try {
                            $transactionDate = \Carbon\Carbon::parse($txn['date']);
                        } catch (\Throwable $e) {
                            $transactionDate = null;
                        }
                    }

                    $receiptAttrs = [
                        'store_name' => $payload['store']['name'] ?? null,
                        'receipt_no' => $receiptNo,
                        'transaction_date' => $transactionDate,
                        'total_items' => $txn['summary']['total_items'] ?? null,
                        'total_discount' => $txn['summary']['total_discount'] ?? null,
                        'subtotal' => $txn['summary']['subtotal'] ?? null,
                        'total_payment' => $txn['summary']['total_payment'] ?? null,
                        'dpp' => $txn['summary']['dpp'] ?? null,
                        'ppn' => $txn['summary']['ppn'] ?? null,
                        'response' => $payload,
                        'status' => ResponseStatus::Completed,
                    ];

                    $document->update($receiptAttrs);

                    $items = $txn['items'] ?? [];
                    foreach ($items as $item) {
                        $document->items()->create([
                            'name' => $item['name'] ?? null,
                            'quantity' => $item['quantity'] ?? 0,
                            'unit_price' => $item['unit_price'] ?? 0,
                            'total_price' => $item['total_price'] ?? 0,
                            'discount' => $item['discount'] ?? 0,
                        ]);
                    }
                });
            }

            // finalize document status
            $document->update(['status' => ResponseStatus::Completed]);
        } catch (\Throwable $e) {
            Log::error('ProcessReceipt failed parsing/persisting', ['id' => $this->documentId, 'error' => $e->getMessage()]);

            $document->update([
                'status' => ResponseStatus::Failed,
                'response' => ['error' => $e->getMessage()],
            ]);
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(Exception $exception): void
    {
        Log::error('ProcessDocument failed permanently', ['id' => $this->documentId, 'error' => $exception->getMessage()]);

        $document = Document::find($this->documentId);
        if ($document) {
            $document->update([
                'status' => ResponseStatus::Failed,
                'response' => ['error' => $exception->getMessage()],
            ]);
        }
    }
}
