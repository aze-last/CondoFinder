<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CondoFinder') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxStyles
    </head>
    <body class="min-h-screen bg-white font-sans text-neutral-900 antialiased dark:bg-neutral-950 dark:text-neutral-100">
        <nav class="sticky top-0 z-50 border-b border-neutral-200 bg-white/80 backdrop-blur-md dark:border-neutral-800 dark:bg-neutral-950/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    <!-- Logo -->
                    <div class="flex shrink-0 items-center gap-2">
                        <a href="{{ route('home') }}" class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-neutral-900 font-bold text-white dark:bg-white dark:text-black">
                                CF
                            </div>
                            <div class="hidden leading-tight sm:block">
                                <div class="text-sm font-bold tracking-tight text-neutral-900 dark:text-white">CondoFinder</div>
                                <div class="text-[10px] text-neutral-500 uppercase tracking-wider font-semibold">Showroom</div>
                            </div>
                        </a>
                    </div>

                    <!-- Search Pill (Airbnb Style) -->
                    <div class="hidden flex-1 justify-center md:flex">
                        <div class="flex w-full max-w-md items-center gap-2 rounded-full border border-neutral-200 bg-white p-1 shadow-sm transition-shadow hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900">
                            <button class="flex flex-1 items-center gap-2 px-4 py-1.5 text-left transition hover:bg-neutral-50 dark:hover:bg-neutral-800 rounded-full">
                                <flux:icon.magnifying-glass class="h-4 w-4 text-neutral-400" />
                                <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">{{ __('Anywhere') }}</span>
                            </button>
                            <div class="h-6 w-px bg-neutral-200 dark:bg-neutral-800"></div>
                            <button class="px-4 py-1.5 text-sm font-medium text-neutral-600 transition hover:bg-neutral-50 dark:text-neutral-400 dark:hover:bg-neutral-800 rounded-full">
                                {{ __('Any Date') }}
                            </button>
                            <div class="h-6 w-px bg-neutral-200 dark:bg-neutral-800"></div>
                            <button class="px-4 py-1.5 text-sm font-medium text-neutral-600 transition hover:bg-neutral-50 dark:text-neutral-400 dark:hover:bg-neutral-800 rounded-full">
                                {{ __('Add Guests') }}
                            </button>
                            <div class="ml-1 rounded-full bg-primary-600 p-2 text-white shadow-sm ring-1 ring-primary-500">
                                <flux:icon.magnifying-glass class="h-4 w-4" />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <div class="hidden sm:flex">
                            @auth
                                <flux:button variant="ghost" size="sm" class="rounded-full" href="{{ route('dashboard') }}" wire:navigate>{{ __('Dashboard') }}</flux:button>
                            @else
                                <flux:button variant="ghost" size="sm" class="rounded-full" href="{{ route('login') }}" wire:navigate>{{ __('Log in') }}</flux:button>
                            @endauth
                        </div>
                        <flux:button variant="outline" size="sm" class="rounded-full border-neutral-200 dark:border-neutral-800">
                            <flux:icon.bars-3 class="h-4 w-4" />
                        </flux:button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        <footer class="border-t border-neutral-200 bg-neutral-50 py-12 dark:border-neutral-800 dark:bg-neutral-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                    <div class="flex items-center gap-2 text-neutral-500">
                        <flux:icon.building-office-2 class="h-6 w-6" />
                        <span class="font-semibold">{{ config('app.name') }}</span>
                        <span class="text-sm">© {{ date('Y') }}</span>
                    </div>
                    <div class="flex gap-6 text-sm text-neutral-500">
                        <a href="#" class="hover:text-primary-600 dark:hover:text-primary-500">{{ __('Privacy') }}</a>
                        <a href="#" class="hover:text-primary-600 dark:hover:text-primary-500">{{ __('Terms') }}</a>
                        <a href="#" class="hover:text-primary-600 dark:hover:text-primary-500">{{ __('Support') }}</a>
                    </div>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
