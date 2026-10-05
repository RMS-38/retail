<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($title) ? config('app.name') . ' - ' . $title : config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @fluxAppearance
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-3">

        <div class="flex items-center justify-between w-full">
            <flux:brand
                href="/"
                logo="retail.png"
                name="Retail" />

            <div>
                <flux:dropdown x-data align="end">
                    <flux:button variant="subtle" square class="group" aria-label="Preferred color scheme">
                        <flux:icon.sun x-show="$flux.appearance === 'light'" variant="mini" class="text-zinc-500 dark:text-white" />
                        <flux:icon.moon x-show="$flux.appearance === 'dark'" variant="mini" class="text-zinc-500 dark:text-white" />
                        <flux:icon.moon x-show="$flux.appearance === 'system' && $flux.dark" variant="mini" />
                        <flux:icon.sun x-show="$flux.appearance === 'system' && ! $flux.dark" variant="mini" />
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Light</flux:menu.item>
                        <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Dark</flux:menu.item>
                        <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">System</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>
    </flux:header>
    <flux:main container>
        <div class="flex max-md:flex-col items-start">
            <div class="w-full md:w-55 pb-4 me-10">
                <flux:navlist>
                    <flux:navlist.item href="/" wire:navigate>Home</flux:navlist.item>
                    <flux:navlist.item href="/products" wire:navigate>Products</flux:navlist.item>
                    <flux:navlist.item href="/commands" wire:navigate>Command</flux:navlist.item>
                    <flux:navlist.item href="/clients" wire:navigate>Client</flux:navlist.item>

                </flux:navlist>
            </div>
            <flux:separator class="md:hidden" />
            <div class="flex-1 max-md:pt-6 self-stretch">
                <flux:heading size="xl" level="1">Good afternoon,Segal</flux:heading>
                <flux:text class="mb-6 mt-2 text-base">Here's what's new today</flux:text>
                <flux:separator variant="subtle" />
                {{ $slot }}
            </div>
        </div>
    </flux:main>
    @livewireScripts
    @fluxScripts
</body>

</html>