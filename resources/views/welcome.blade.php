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
<body class="bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-100 antialiased">
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
        <!-- Hero -->
        <section class="container mx-auto px-8 pt-6 pb-12">
            <div class="grid lg:grid-cols-2 items-center gap-8">
                <div class="space-y-4">
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">Free, open source, and powerful</p>
                    <h1 class="text-4xl lg:text-5xl font-semibold leading-tight">Explore AI models in one clean place</h1>
                    <p class="text-zinc-600 dark:text-zinc-400 max-w-xl">Demolite is a free, open-source collection of AI-powered mini apps for real-world tasks. From chatbots to file validation and resume analysis—all powered by OpenAI. Try the live demo or explore the source code on GitHub.</p>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="{{ route('chat.bot-ai') }}" class="px-6 py-3 rounded-md text-white bg-black hover:bg-zinc-900 transition-colors">Start Exploring</a>
                        <a href="https://github.com/chrislorando/demolite" target="_blank" class="px-6 py-3 rounded-md border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-800/5 transition-colors">Learn More</a>
                    </div>
                </div>
                <div class="relative">
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 shadow-xs overflow-hidden">
                        <img src="{{ asset('screenshot.png') }}" alt="Demolite AI Playground demo" class="w-full object-cover" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="container mx-auto px-8 mt-4">
            <div class="grid md:grid-cols-3 gap-6">
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 p-8">
                    <flux:icon name="currency-dollar" class="size-8 mb-4 text-zinc-700 dark:text-zinc-300" />
                    <h3 class="font-semibold mb-2 text-lg">100% Free</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Enjoy all AI features completely free. No hidden fees or usage limitations.</p>
                </div>
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 p-8">
                    <flux:icon name="squares-2x2" class="size-8 mb-4 text-zinc-700 dark:text-zinc-300" />
                    <h3 class="font-semibold mb-2 text-lg">Multi Features</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Complete collection of AI-powered mini apps for various needs: chatbots, file analysis, resume review, and more.</p>
                </div>
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 p-8">
                    <flux:icon name="code-bracket" class="size-8 mb-4 text-zinc-700 dark:text-zinc-300" />
                    <h3 class="font-semibold mb-2 text-lg">Open Source</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Open source code on GitHub. Contribute, modify, and deploy according to your needs.</p>
                </div>
            </div>
        </section>

        <!-- Preview / Showcase -->
        <section class="container mx-auto px-8 mt-10">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 overflow-hidden">
                <div class="px-8 py-6">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="inline-block size-3 rounded-full bg-neutral-200 dark:bg-zinc-800"></span>
                        <span class="inline-block size-3 rounded-full bg-neutral-200 dark:bg-zinc-800"></span>
                        <span class="inline-block size-3 rounded-full bg-neutral-200 dark:bg-zinc-800"></span>
                    </div>
                    <div class="grid lg:grid-cols-2 gap-6">
                        <div class="rounded-md border border-zinc-200 dark:border-zinc-700 p-0 overflow-hidden">
                            <img src="https://picsum.photos/seed/showcase-1/1024/600" alt="Showcase left" class="w-full h-64 object-cover" />
                        </div>
                        <div class="rounded-md border border-zinc-200 dark:border-zinc-700 p-0 overflow-hidden">
                            <img src="https://picsum.photos/seed/showcase-2/1024/600" alt="Showcase right" class="w-full h-64 object-cover" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="container mx-auto px-8 mt-10">
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 p-8 flex items-center justify-between max-md:flex-col gap-6">
                <div>
                    <h3 class="font-semibold mb-1 text-xl">Ready to build with AI?</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Open the playground and start exploring different AI apps now.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('chat.bot-ai') }}" class="px-6 py-3 rounded-md text-white bg-black hover:bg-zinc-900 transition-colors">Start Exploring</a>
                    <a href="https://github.com/chrislorando/demolite" target="_blank" class="px-6 py-3 rounded-md border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-800/5 transition-colors">Learn More</a>
                </div>
            </div>
        </section>
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
