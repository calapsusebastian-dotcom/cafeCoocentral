@props(['title', 'subtitle' => '', 'icon' => 'shopping-cart'])

@php
    $vendor = \Illuminate\Support\Facades\Auth::user();
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Café Coocentral</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-gray-50 text-gray-900">
    <div class="flex min-h-screen" x-data="{ sidebarOpen: false }">
        <x-layouts.partials.sidebar :vendor="$vendor" />

        <div class="flex-1 min-w-0 flex flex-col">
            <x-layouts.partials.topbar :title="$title" :subtitle="$subtitle" :icon="$icon">
                {{ $headerActions ?? '' }}
            </x-layouts.partials.topbar>

            <main class="flex-1 px-4 sm:px-6 lg:px-10 py-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
