<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('chat-bot-ai', \App\Livewire\Chat\ChatBotAi::class)
    ->middleware(['auth', 'verified'])
    ->name('chat.bot-ai');

Route::get('chat', function () {
    return redirect()->route('chat.bot-ai');
})->middleware(['auth', 'verified']);

Route::get('chat-bot-ai/new', \App\Livewire\Chat\ChatBotAi::class)
    ->middleware(['auth', 'verified'])
    ->name('chat.bot-ai.new');

Route::get('chat-bot-ai/{id}', \App\Livewire\Chat\ChatBotAi::class)
    ->middleware(['auth', 'verified'])
    ->name('chat.bot-ai.show');


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
