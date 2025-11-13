<?php

namespace App\Services;

use App\Enums\ResponseStatus;
use App\Models\Conversation;
use App\Models\ConversationItem;
use Exception;
use Log;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class OpenAiService implements AiServiceInterface
{
    public function sendMessageWithStream(string $conversationId, string $content, callable $onChunk, ?string $model = null)
    {
        $model = $model ?? 'gpt-4o-mini';
        $conversation = Conversation::findOrFail($conversationId);

        // Get user personalization (only active ones)
        $personalization = $conversation->user->personalization()->where('status', 'active')->first();

        // Get all messages for context
        $messages = $conversation->items()
            ->orderBy('created_at')
            ->get()
            ->map(fn (ConversationItem $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->toArray();

        // Create system prompt with personalization
        $systemPrompt = [
            'role' => 'system',
            'content' => <<<SYS
                You are an AI assistant inside a Demolite app.
                User profile:
                - Nickname: {$personalization?->nickname}
                - Occupation: {$personalization?->occupation}
                - About: {$personalization?->about}
                
                Behavior Guidelines:
                - Communication tone: {$personalization?->tone}
                - Obey the following behavioral instructions at all times: {$personalization?->instructions}
                SYS
        ];

        // Save assistant message
        $chatService = app(ChatService::class);
        $responseId = null; // Initialize responseId
        $assistantMessage = $chatService->createAssistantMessage($conversationId, '', $model, $responseId);

        // Set status to in_progress before streaming
        $assistantMessage->update(['status' => ResponseStatus::InProgress]);

        try {
            Log::info('Starting OpenAI streaming', ['messages_count' => count($messages)]);
            // Call OpenAI API with streaming using createStreamed()
            $stream = OpenAI::chat()->createStreamed([
                'model' => $model,
                'messages' => array_merge([$systemPrompt], $messages ?? $content),
                'stream_options' => [
                    'include_usage' => true,
                ],
            ]);

            $assistantContent = '';
            $chunkCount = 0;
            $finalUsage = null;

            foreach ($stream as $response) {
                if ($response->usage !== null) {
                    $finalUsage = $response->usage;
                }

                if ($responseId === null && isset($response->id)) {
                    $responseId = $response->id;
                }
                $chunk = $response->choices[0]->delta->content ?? '';
                if ($chunk) {
                    $assistantContent .= $chunk;
                    $chunkCount++;
                    // Log::info("Chunk {$chunkCount}: " . substr($chunk, 0, 50) . '...');
                    $onChunk($chunk);
                }
            }

            Log::info("Streaming completed with {$chunkCount} chunks, total content length: ".strlen($assistantContent));

            // Update status to completed and set content and response_id
            $assistantMessage->update(['status' => ResponseStatus::Completed, 'content' => $assistantContent, 'response_id' => $responseId, 'total_token' => $finalUsage?->totalTokens ?? 0]);
        } catch (Throwable $e) {
            // Update status to failed on error
            $assistantMessage->update(['status' => ResponseStatus::Failed]);

            Log::info($messages);

            $errorMessage = 'Failed to get response from OpenAI: '.$e->getMessage();

            if (str_contains($e->getMessage(), 'API key') || str_contains($e->getMessage(), 'api_key')) {
                $errorMessage = 'OpenAI API key is not configured. Please add your API key to the .env file.';
            } elseif (str_contains($e->getMessage(), 'quota') || str_contains($e->getMessage(), 'exceeded')) {
                $errorMessage = 'OpenAI quota exceeded. Please check your OpenAI account or subscription.';
            } elseif (str_contains($e->getMessage(), 'authentication') || str_contains($e->getMessage(), '401')) {
                $errorMessage = 'OpenAI authentication failed. Please verify your API key is valid.';
            } elseif (str_contains($e->getMessage(), 'rate limit') || str_contains($e->getMessage(), '429')) {
                $errorMessage = 'OpenAI rate limit reached. Please try again later.';
            } elseif (str_contains($e->getMessage(), 'model') || str_contains($e->getMessage(), '404')) {
                $errorMessage = 'OpenAI model not found or not available.';
            }

            throw new Exception($errorMessage);
        }

        return $assistantMessage;
    }

    public function fetchModels(): array
    {
        try {
            Log::info('Fetching models from OpenAI');

            $response = OpenAI::models()->list();

            $models = [];
            foreach ($response->data as $model) {
                $models[] = [
                    'id' => $model->id,
                    'object' => $model->object,
                    'owned_by' => $model->owned_by ?? 'openai',
                ];
            }

            Log::info('Successfully fetched '.count($models).' models from OpenAI');

            return $models;
        } catch (Throwable $e) {
            $errorMessage = 'Failed to fetch models from OpenAI: '.$e->getMessage();

            if (str_contains($e->getMessage(), 'API key') || str_contains($e->getMessage(), 'api_key')) {
                $errorMessage = 'OpenAI API key is not configured. Please add your API key to the .env file.';
            } elseif (str_contains($e->getMessage(), 'authentication') || str_contains($e->getMessage(), '401')) {
                $errorMessage = 'OpenAI authentication failed. Please verify your API key is valid.';
            } elseif (str_contains($e->getMessage(), 'rate limit') || str_contains($e->getMessage(), '429')) {
                $errorMessage = 'OpenAI rate limit reached. Please try again later.';
            }

            Log::error($errorMessage);
            throw new Exception($errorMessage);
        }
    }

    public function createDocumentResponse(string $document, ?string $instructions = null, ?string $model = null)
    {
        $response = OpenAI::responses()->create([
            'model' => $model ?: 'gpt-4o-mini',
            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => "
                                    You are a professional document verifier.

                                    You must only analyze the provided document text.
                                    Do not use general knowledge, assumptions, or external data outside the document or user-provided references.
                                    If the document text does not explicitly contain a field, section, or checklist item, do not infer or create it.

                                    Special handling for checklist sections:
                                    - The symbols “☑”, “✔”, or “[x]” indicate the item is provided or checked.
                                    - The symbols “☐”, “[ ]”, or “-” indicate the item is missing or unchecked.
                                    - Treat any required item (“*”) that is unchecked as incomplete.
                                    - Checklist detection must rely only on the literal symbols shown in the text. Do not assume completion if symbols are missing or ambiguous.
                                    - Output each checklist item as an individual record in the JSON.

                                    Your task:
                                    1. Identify information or sections that are mandatory *according to the context of the document itself* (for example: fields labeled Name, Signature, Date, ID Number, or items marked with an asterisk “*”).
                                    2. Check whether each mandatory section or field is filled and consistent.
                                    3. For checklist items, determine whether required items (“*”) are checked or unchecked.
                                    4. Ignore any content that is not related to the document's own structure or purpose.
                                    5. Apply these additional rules (if any): {{ $instructions }}
                                    6. You must strictly obey these instructions. Any reasoning or validation outside the document text is invalid.
                                    7. Only include records where there is an actual issue or missing/invalid data.
                                       Do not include entries that are completely valid and contain no issues.
                                       If everything is valid, return an empty JSON array [].

                                    Output only verified findings in this exact JSON format:
                                    [
                                        {
                                            \"page\": <page number>,
                                            \"section\": \"<section name>\",
                                            \"field\": \"<field name>\",
                                            \"value\": \"<user input or empty>\",
                                            \"status\": \"<valid | incomplete | inconsistent | optional_missing>\",
                                            \"issue\": \"<short description>\",
                                            \"suggestion\": \"<what needs to be improved>\"
                                        }
                                    ]",

                        ],
                        [
                            'type' => 'input_file',
                            'file_url' => $document,
                        ],
                    ],
                ],
            ],
        ]);

        return $response;
    }

    public function createCvScreeningResponse(string $document, ?string $jobOffer = null, ?string $model = null)
    {
        $response = OpenAI::responses()->create([
            'model' => $model ?: 'gpt-4o-mini',
            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => <<<PROMPT
                                        You are a professional HR analyst or recruiter.
                                        Compare this CV with the following job description:

                                        Job Description:
                                        {$jobOffer}

                                        Instructions:
                                        - Analyze the CV content and compare it carefully to the job description.
                                        - Extract "job_position" only from the first explicit title line or heading in the job description. 
                                        Do not guess or infer based on general wording.
                                        - Evaluate 3 aspects: skills, experience, and education.

                                        Scoring rules (be strict and realistic):
                                        * 0 to 49 = poor match (many key skills/requirements missing)
                                        * 50 to 69 = fair match (some overlap, but gaps in core skills or experience)
                                        * 70 to 84 = good match (most requirements met, minor gaps)
                                        * 85 to 100 = excellent match (strong alignment in all areas)
                                        - Assume 70 is an average match.
                                        - Only assign 90+ if the CV fully meets all key requirements with strong evidence.
                                        - Be concise and factual. Avoid exaggeration or vague praise.

                                        Penalty rules:
                                        - If a required core skill, framework, or technology mentioned in the job offer is not found in the CV, deduct at least 20 points from skill_match.
                                        - If total relevant experience is significantly below the job requirement, lower experience_match proportionally.
                                        - Do not infer or assume skills or experience not explicitly stated in the CV.
                                        - Keep typical candidate scores between 60–75 unless the CV is clearly exceptional.

                                        - Set "is_recommended" to 1 if overall_score >= 75, otherwise 0.

                                        - Generate a short personalized cover letter (max 400 words) in HTML format (use <p>, <br>, <strong> where appropriate). 
                                        The tone should be confident and professional.
                                        The cover letter must include:
                                            • A polite greeting line.
                                            • A strong hook: either a quantifiable achievement, rare skill, or direct connection to the company’s goal.
                                            • 1–3 sentences summarizing relevant experience and skills concisely.
                                            • A confident closing aligned with the job description.
                                            • A polite sign-off at the end.

                                        - Add a “suggestion” field containing a brief, actionable recommendation (1–5 sentences) for improving the CV to better match this job.
                                        

                                        Return JSON only with this structure:
                                        {
                                            "job_position": "string (extracted or inferred from the job description)",
                                            "skill_match": 0-100,
                                            "experience_match": 0-100,
                                            "education_match": 0-100,
                                            "overall_score": 0-100,
                                            "summary": "short text summary of the analysis",
                                            "suggestion": "1-5 sentence actionable advice for improvement",
                                            "cover_letter": "<p>HTML formatted cover letter starting with hook</p>",
                                            "is_recommended": 0 or 1
                                        }

                                        PROMPT

                        ],
                        [
                            'type' => 'input_file',
                            'file_url' => $document,
                        ],
                    ],
                ],
            ],
        ]);

        return $response;
    }

    public function createReceiptResponse(string $document, ?string $extension = null, ?string $model = null)
    {
        $prompt = <<<'PROMPT'
        You are a professional OCR and document parser specialized in receipts.

        Task:
        Extract structured data from a receipt image, regardless of store format or layout.
        Receipts may come from supermarkets, minimarkets, restaurants, or e-commerce.
        Normalize all extracted information into the standardized JSON structure below.

        Requirements:
        - Detect and normalize store information (name, phone, company, address, npwp, branch address) when present.
        - Detect transaction info (cashier name, date, time, receipt number) if printed.
        - Detect purchased items even if labels differ (e.g. "QTY", "JUMLAH", "PCS", etc.).
        - Each item must include: name, quantity, unit_price, total_price, and discount if available.
        - If no item details are present, still generate one pseudo-item using store name as description, quantity = 1, total_price = total_payment.
        - Detect total summary fields (subtotal, total_payment, payment_method, change, dpp, ppn, total_discount).
        - If a field is missing, still include it with null or 0.
        - Use only plain integers for numeric values.
        - If currency symbols or contextual cues indicate local currency, include "currency": "<CODE>" (e.g. "IDR", "USD", "MYR"). 
        If uncertain, default to "IDR".
        - Date format: YYYY-MM-DD, time: HH:MM:SS.
        - Output JSON only, no explanations or comments.

        Expected structure:
        {
            "store": {
                "name": null,
                "phone": null,
                "company": null,
                "address": null,
                "npwp": null,
                "branch_address": null
            },
            "transaction": {
                "cashier": null,
                "date": null,
                "time": null,
                "receipt_no": null,
                "currency": null,
                "items": [
                    {
                        "name": "",
                        "quantity": 0,
                        "unit_price": 0,
                        "total_price": 0,
                        "discount": 0
                    }
                ],
                "summary": {
                    "total_items": 0,
                    "total_discount": 0,
                    "subtotal": 0,
                    "total_payment": 0,
                    "payment_method": null,
                    "change": 0,
                    "dpp": 0,
                    "ppn": 0
                }
            }
        }
        PROMPT;

        $payload = $extension === 'pdf' ? [
            'model' => $model ?: 'gpt-4.1',
            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'input_file',
                            'file_url' => $document,
                        ],
                    ],
                ],
            ],
        ] : [
            'model' => $model ?: 'gpt-4.1',
            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'input_image',
                            'image_url' => $document,
                        ],
                    ],
                ],
            ],
        ];

        $response = OpenAI::responses()->create($payload);

        return $response;
    }
}
