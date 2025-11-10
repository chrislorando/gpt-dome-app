<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demolite - AI Playground — Free AI model sandbox</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @vite(['resources/css/app.css'])
</head>
<body class="bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-100 antialiased overflow-hidden">
    
    <header class="container mx-auto px-8 py-6">
        <nav class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="/logo.jpeg" alt="{{ config('app.name') }} logo" class="size-9 rounded-md object-cover" />
                <span class="font-semibold text-lg">{{ config('app.name') }}</span>
            </a>
            @if (Route::has('login'))
                <div class="flex items-center gap-3 text-sm">
                    @auth
                        <flux:link wire:navigate :href="route('dashboard')" variant="ghost" class="px-5 py-1.5 rounded-sm border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-800/5">Dashboard</flux:link>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-1.5 rounded-sm border border-transparent hover:border-zinc-200 dark:hover:border-zinc-700">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-1.5 rounded-sm border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-800/5">Register</a>
                        @endif
                    @endauth
                </div>
            @endif
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="container mx-auto px-8 my-8 border-t border-zinc-200 dark:border-zinc-800 pt-6">
        <div class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-400 max-md:flex-col gap-3">
            <p>© {{ date('Y') }} Demolite. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="https://medium.com/@twinklescode" target="_blank" class="hover:text-zinc-800 dark:hover:text-white">Medium</a>
                <a href="https://github.com/chrislorando" target="_blank" class="hover:text-zinc-800 dark:hover:text-white">GitHub</a>
                <a href="linkedin.com/in/chrismanuellorando/" target="_blank" class="hover:text-zinc-800 dark:hover:text-white">LinkedIn</a>
            </div>
        </div>
    </footer>
</body>
</html>
