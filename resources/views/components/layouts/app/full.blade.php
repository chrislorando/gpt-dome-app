
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="md:flex min-h-screen bg-white dark:bg-zinc-800 {{ request()->routeIs('profile.edit') || request()->routeIs('personalization.edit') ? '' : 'overflow-hidden' }}">

        <!-- Sidebar -->
        <flux:sidebar sticky collapsible class="h-screen w-64 border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 px-2">
            <flux:sidebar.header class="w-full z-10 bg-zinc-50 dark:bg-zinc-900">
                <flux:sidebar.brand
                    href="#"
                    logo="https://ai-playground-app.test/logo.jpeg"
                    logo:dark="https://ai-playground-app.test/logo.jpeg"
                    name="{{ config('app.name') }}"
                />
     
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:sidebar.item>
                <flux:sidebar.item icon="chat-bubble-left-right" :href="route('chat.bot-ai.new')" :current="request()->routeIs('chat.bot-ai.new')" wire:navigate>{{ __('New chat') }}</flux:sidebar.item>
                <flux:sidebar.item icon="magnifying-glass" x-on:click="$dispatch('open-search-modal')">{{ __('Search chats') }}</flux:sidebar.item>
            </flux:sidebar.nav>

            <div class="flex-1 overflow-y-auto overflow-x-hidden w-full px-0">
                <flux:sidebar.nav>

                    <flux:navlist variant="outline">
                        <flux:sidebar.group icon="chat-bubble-left-right" :heading="__('Chats')" class="grid text-xs font-semibold">
                            @livewire('chat.conversation-list')
                        </flux:sidebar.group>
                    </flux:navlist>

                </flux:sidebar.nav>
            </div>

            {{-- <flux:spacer /> --}}

            {{-- <flux:navlist variant="outline">
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:sidebar.item> 

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:navlist> --}}
            
            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden md:block sticky bottom-0 z-10" position="bottom" align="start">
                <flux:sidebar.profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                    data-test="sidebar-menu-button"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
            
        </flux:sidebar>

        <div class="flex-1 flex flex-col min-h-screen">
            <!-- Header -->
            <flux:header sticky container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                <flux:navbar scrollable>
                    <flux:sidebar.item icon="document-magnifying-glass" :href="route('documents.index')" :current="request()->routeIs('documents.index')" wire:navigate>{{ __('Document Verifier') }}</flux:sidebar.item>
                    <flux:navbar.item href="#">CV Screening</flux:navbar.item>
                    <flux:navbar.item href="#">Expense Tracker</flux:navbar.item>
                </flux:navbar>
                
                <flux:spacer />

                <!-- Desktop User Menu -->
                <flux:dropdown position="top" align="end" class="lg:hidden">
                    <flux:profile
                        class="cursor-pointer"
                        :initials="auth()->user()->initials()"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:header>

            <!-- Main Content -->
            {{ $slot }}
        </div>
        @livewire('chat.search-modal')
        @fluxScripts
    </body>
</html>
