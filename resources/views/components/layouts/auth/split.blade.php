<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-neutral-900"></div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium" wire:navigate>
                    <img src="/logo.png" alt="{{ config('app.name') }} logo" class="me-3 h-10 w-10 rounded-md object-cover" />
                    {{ config('app.name', 'Laravel') }}
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading>{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>

                <!-- Eyes Follow Animation - Cat Face -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="flex flex-col items-center gap-6">
                        <!-- Cat Face -->
                        <div id="catFace" class="relative w-32 h-40 rounded-3xl border-4 border-white flex flex-col items-center justify-center" data-has-errors="{{ $errors->any() ? 'true' : 'false' }}">
                            <!-- Left Ear -->
                            <div class="absolute -top-3 left-4 w-0 h-0 border-l-4 border-r-4 border-b-8 border-l-transparent border-r-transparent border-b-white"></div>
                            <!-- Right Ear -->
                            <div class="absolute -top-3 right-4 w-0 h-0 border-l-4 border-r-4 border-b-8 border-l-transparent border-r-transparent border-b-white"></div>

                            <!-- Eyes Container -->
                            <div class="flex gap-8 mb-6">
                                <!-- Left Eye -->
                                <div class="relative w-10 h-12 bg-white rounded-full flex items-center justify-center">
                                    <div class="eyes-pupil w-3 h-5 bg-neutral-900 rounded-full absolute" style="left: 50%; top: 50%; transform: translate(-50%, -50%);"></div>
                                </div>
                                <!-- Right Eye -->
                                <div class="relative w-10 h-12 bg-white rounded-full flex items-center justify-center">
                                    <div class="eyes-pupil w-3 h-5 bg-neutral-900 rounded-full absolute" style="left: 50%; top: 50%; transform: translate(-50%, -50%);"></div>
                                </div>
                            </div>

                            <!-- Whiskers -->
                            <div class="absolute left-0 top-1/2 flex flex-col gap-2">
                                <div class="w-6 h-px bg-white"></div>
                                <div class="w-6 h-px bg-white"></div>
                                <div class="w-6 h-px bg-white"></div>
                            </div>
                            <div class="absolute right-0 top-1/2 flex flex-col gap-2">
                                <div class="w-6 h-px bg-white"></div>
                                <div class="w-6 h-px bg-white"></div>
                                <div class="w-6 h-px bg-white"></div>
                            </div>

                            <!-- Nose -->
                            <div class="w-2 h-2 bg-white rounded-full mb-2 mt-2"></div>

                            <!-- Mouth (Cat smile) -->
                            <svg id="catMouth" class="w-10 h-6 mt-1 transition-opacity duration-300" viewBox="0 0 100 50" xmlns="http://www.w3.org/2000/svg">
                                <path d="M 50 30 Q 40 40 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
                                <path d="M 50 30 Q 60 40 70 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
                            </svg>
                            <!-- Sad Mouth (hidden by default) -->
                            <svg id="catSadMouth" class="w-10 h-6 mt-1 transition-opacity duration-300 opacity-0 absolute" viewBox="0 0 100 50" xmlns="http://www.w3.org/2000/svg">
                                <path d="M 50 20 Q 40 10 30 15" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
                                <path d="M 50 20 Q 60 10 70 15" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <!-- Meoow Message -->
                        <div id="meoowMessage" class="text-xl font-bold text-white transition-opacity duration-300 opacity-0">Meoow 😿</div>
                    </div>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex items-center justify-center gap-3 font-medium lg:hidden" wire:navigate>
                        <img src="/logo.png" alt="{{ config('app.name') }} logo" class="size-9 rounded-md object-cover" />
                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts

        <script>
            const catFace = document.getElementById('catFace');
            const catMouth = document.getElementById('catMouth');
            const catSadMouth = document.getElementById('catSadMouth');
            const meoowMessage = document.getElementById('meoowMessage');
            const hasErrors = catFace?.dataset.hasErrors === 'true';
            let errorTimeout;

            function showSadCat() {
                catMouth.classList.add('opacity-0');
                catSadMouth.classList.remove('opacity-0');
                meoowMessage.classList.remove('opacity-0');

                // Reset to happy after 5 seconds
                clearTimeout(errorTimeout);
                errorTimeout = setTimeout(() => {
                    showHappyCat();
                }, 5000);
            }

            function showHappyCat() {
                catMouth.classList.remove('opacity-0');
                catSadMouth.classList.add('opacity-0');
                meoowMessage.classList.add('opacity-0');
                clearTimeout(errorTimeout);
            }

            // Set initial sad expression if there are errors
            if (hasErrors) {
                showSadCat();
            }

            // Listen for form submission to show sad face on error
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', () => {
                    // Small delay to check if errors exist after submission attempt
                    setTimeout(() => {
                        if (catFace?.dataset.hasErrors === 'true' || document.querySelector('[role="alert"]')) {
                            showSadCat();
                        } else {
                            showHappyCat();
                        }
                    }, 100);
                });
            }

            document.addEventListener('mousemove', (e) => {
                const pupils = document.querySelectorAll('.eyes-pupil');
                const mouseX = e.clientX;
                const mouseY = e.clientY;

                pupils.forEach(pupil => {
                    const eye = pupil.parentElement;
                    const eyeRect = eye.getBoundingClientRect();
                    const eyeCenterX = eyeRect.left + eyeRect.width / 2;
                    const eyeCenterY = eyeRect.top + eyeRect.height / 2;

                    const angle = Math.atan2(mouseY - eyeCenterY, mouseX - eyeCenterX);
                    const distance = eyeRect.width / 2 - pupil.offsetWidth / 2 - 2;

                    const pupilX = Math.cos(angle) * distance;
                    const pupilY = Math.sin(angle) * distance;

                    pupil.style.transform = `translate(calc(-50% + ${pupilX}px), calc(-50% + ${pupilY}px))`;
                });
            });
        </script>
    </body>
</html>
