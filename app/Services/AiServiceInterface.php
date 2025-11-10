<?php

namespace App\Services;

interface AiServiceInterface
{
    /**
     * Send a message with streaming response
     *
     * @param  callable  $onChunk  Callback function that receives each chunk
     * @param  string|null  $model  Model to use for the request
     * @return mixed
     */
    public function sendMessageWithStream(string $conversationId, string $content, callable $onChunk, ?string $model = null);

    /**
     * Cancel a model response with the given ID
     */
    public function cancelResponse(string $responseId): array;

    /**
     * Send a file to be checked
     *
     * @return mixed
     */
    public function createDocumentResponse(string $document, ?string $instruction = null, ?string $model = null);
}
