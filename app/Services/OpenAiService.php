<?php

namespace App\Services;

use App\Enums\ResponseStatus;
use Exception;
use Log;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class OpenAiService implements AiServiceInterface
{
    public function __construct(
        protected ChatService $chatService
    ) {}


    public function sendMessageWithStream(string $conversationId, string $content, callable $onChunk, ?string $model = null)
    {
        $model = $model ?? 'gpt-4o-mini';
        $personalization = $this->chatService->getPersonalizationForConversation($conversationId);
        $messages = $this->chatService->getConversationMessages($conversationId);

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

        $responseId = null;
        $assistantMessage = $this->chatService->createAssistantMessage($conversationId, '', $model, $responseId);
        $assistantMessage->update(['status' => ResponseStatus::InProgress]);

        try {
            $inputMessages = array_merge([
                $systemPrompt
            ], $messages);
            $payload = [
                //  "prompt" => [
                //     "id" => "pmpt_69026f7b8f288196a5e77abcb679ef990d7724d104e55cb6",
                //     "version" => "1"
                // ],
                // "tools" => [
                //     [
                //         'type' => 'mcp',
                //         'allowed_tools' => [
                //             'list-projects',
                //             'create-project',
                //             'update-project',
                //             'delete-project',
                //             'search-projects',
                //             'list-tasks',
                //             'create-task',
                //             'update-task',
                //             'complete-task',
                //             'delete-task',
                //             'search-tasks'
                //         ],
                //         'headers' => [
                //             'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIwMTlhM2E4OS1kMTg5LTczZDEtOTEzNi01ODkxMWVlMDMzMjciLCJqdGkiOiJiNDA1NTlkNzkwMmJmZGNlMDU0ZDhhZDFmYjYzODUzMmJjZmQ3YzhhODc5N2U1NzM2ZTNjNTZkOTBmZGNkNTEzNGNiNmFhNDEwMTFhMWM0NyIsImlhdCI6MTc2NDE3Mzc0OS4wNTk1MTksIm5iZiI6MTc2NDE3Mzc0OS4wNTk1MjMsImV4cCI6MTc5NTcwOTc0OC45NDQ1Mywic3ViIjoiOTgxZWMxNzgtZGRlZC00YzQyLTg3MDgtYmRmYTI1MzZmOTNkIiwic2NvcGVzIjpbXX0.UuaTfYVYIOMHPQwULWNN9DMEp1SVi1eIN3ik8bA4d4Mx-VKX_c99jLbIs-VeKdqCBguK0Ek9ysBmGzdthtNACJJVPyP3rrdKbOMUWNBRWfkF-DalIyHfoJf90CCjlB1abHJ--gwrJeHAvXLo0b0JycZDCquZE4FLvz78xxAGyHPtyXg1oKbPMdjnktGUPu8US3xouXtj3oLVL5PxkwiCFuK7KfP-hRZNde9X4SoObunyqE-4CCtNUSCPxBEfxkjgdjgt5lfL2GBkS1WxDfT0sP3ir1Wsm7F12h0jF80yeTbUAYmlAm_FjRIFBNDS54Ade_XWKgAyEpmjys0eFkNZMmIqiRflqPDqXkxV_LoREDfxO3ehEbdxPtamNUkfAKPLQVXxANNNhxP8YhYI7KDG2vG_H8drxp2vcXZ41dh0PL-HwcWz4Xk53O-F2xbjehp4bmhYdEO5gyDj7JtB-KPlUsZKGPTnSWiRKmXe-z68WRYbsvPonovJYIRZTmjvedHrSF4AfdWhAD2-KpS_DUO0HNDS_cZIJLCVkNGF01x5eh42dkWaGCHL2DMh0ZhjZ4cVBWBG5T4PCa2XPRBPA_IIEDAWRBpOxsAJ9p6n6Hy4gQYn5w1E3PlRElVmnNkS0bBTBAAB3db8JAGpZ69oBvnYv_yVCcglxoachn8UiocTdKE'
                //         ],
                //         'require_approval' => 'never',
                //         'server_description' => null,
                //         'server_label' => 'notedpak_mcp_server',
                //         'server_url' => 'https://notedpak.demolite.my.id/mcp/todo'
                //     ]
                // ],
                'model' => $model,
                'input' => $inputMessages,
                'stream' => true,
                'temperature' => 0.7,
                'max_output_tokens' => 2048,
            ];
            try {
                $stream = OpenAI::responses()->createStreamed($payload);
            } catch (Throwable $e) {
                if (str_contains($e->getMessage(), "Unsupported parameter: 'temperature' is not supported with this model")) {
                    unset($payload['temperature']);
                    $stream = OpenAI::responses()->createStreamed($payload);
                } else {
                    throw $e;
                }
            }

            $assistantContent = '';
            $chunkCount = 0;
            $finalUsage = null;

            foreach ($stream as $response) {
                $event = $response->event ?? null;
                $payloadArr = (array) $response->toArray();

                if (isset($payloadArr['usage'])) {
                    $finalUsage = (array) $payloadArr['usage'];
                }
                if ($responseId === null && isset($payloadArr['id'])) {
                    $responseId = $payloadArr['id'];
                }

                if ($event === 'response.output_text.delta' && isset($payloadArr['data']['delta'])) {
                    $delta = $payloadArr['data']['delta'];
                    $assistantContent .= $delta;
                    $chunkCount++;
                    $onChunk($delta);
                }
            }

            Log::info("Streaming completed with {$chunkCount} chunks, total content length: ".strlen($assistantContent));

            $assistantMessage->update([
                'status' => ResponseStatus::Completed,
                'content' => $assistantContent,
                'response_id' => $responseId,
                'total_token' => $finalUsage['totalTokens'] ?? 0
            ]);
        } catch (Throwable $e) {
            $assistantMessage->update(['status' => ResponseStatus::Failed]);
            Log::info(json_encode($messages));
            $errorMessage = 'Failed to get response from OpenAI: '.$e->getMessage();

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
                $models[] = $model;
            }

            Log::info('Successfully fetched '.count($models).' models from OpenAI');

            return $models;
        } catch (Throwable $e) {
            $errorMessage = 'Failed to fetch models from OpenAI: '.$e->getMessage();

            Log::error($errorMessage);
            throw new Exception($errorMessage);
        }
    }

    public function retrieveModel($model): array
    {
        try {
            Log::info('Fetching model info');

            $result = (array) OpenAI::models()->retrieve($model)->toArray();

            Log::info('Successfully fetched model info from OpenAI');

            return $result;
        } catch (Throwable $e) {
            $errorMessage = 'Failed to fetch models from OpenAI: '.$e->getMessage();

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

    public function formatTranscript(string $rawTranscript): string
    {
        $prompt = "You are a helpful assistant that formats voice transcripts into structured notes.\n\nAnalyze this transcript and extract:\n1. A clear summary (2-3 sentences or 1-2 paragraphs depending on length)\n2. Key points (as bullet points)\n3. Action items with due dates if mentioned (format: 'Action: [task] | Due: [date or 'Not specified']')\n\nFormat the output clearly with headers.\n\nTranscript: {$rawTranscript}";

        $response = OpenAI::responses()->create([
            'model' => 'gpt-4o-mini',
            'input' => $prompt,
        ]);

        // Output is in $response->output[0]['content'][0]['text']
        $output = $response->output[0]['content'][0]['text'] ?? '';
        return $output;
    }
}
