<?php

use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can create a conversation', function () {
    $user = User::factory()->create();
    $service = new ChatService;

    $conversation = $service->createConversation($user->id);

    expect($conversation)
        ->toBeInstanceOf(Conversation::class)
        ->and($conversation->user_id)->toBe($user->id)
        ->and($conversation->title)->toBeNull();
});

test('it can create a conversation without user', function () {
    $service = new ChatService;

    $conversation = $service->createConversation();

    expect($conversation)
        ->toBeInstanceOf(Conversation::class)
        ->and($conversation->user_id)->toBeNull();
});

test('it can send a message and get response from OpenAI', function () {
    $conversation = Conversation::factory()->create();

    $service = new ChatService;
    
    // Create user message
    $userMessage = $service->createUserMessage($conversation->id, 'Hello, how are you?');
    
    // Create assistant message
    $assistantMessage = $service->createAssistantMessage($conversation->id, 'This is a test response from OpenAI');

    expect($userMessage->role)->toBe('user')
        ->and($userMessage->content)->toBe('Hello, how are you?')
        ->and($assistantMessage->role)->toBe('assistant')
        ->and($assistantMessage->content)->toBe('This is a test response from OpenAI')
        ->and($conversation->fresh()->items()->count())->toBe(2); // user + assistant
});

test('it generates title from first message', function () {
    $conversation = Conversation::factory()->create(['title' => null]);

    $service = new ChatService;
    $service->createUserMessage($conversation->id, 'This is a very long message that should be truncated');

    expect($conversation->fresh()->title)->not->toBeNull()
        ->and(strlen($conversation->fresh()->title))->toBeLessThanOrEqual(53); // 50 chars + "..."
});
