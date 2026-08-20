@props(['title', 'subtitle' => '', 'icon' => 'shopping-cart'])

<header class="flex items-center justify-between gap-4 px-6 lg:px-10 py-6 border-b border-gray-100 bg-white/70 backdrop-blur sticky top-0 z-10">
    <div class="flex items-center gap-3 min-w-0">
        <span class="hidden sm:flex items-center justify-center w-11 h-11 rounded-2xl bg-brand-50 text-brand-600 shrink-0">
            <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-6 h-6" />
        </span>
        <div class="min-w-0">
            <h1 class="text-xl font-semibold text-gray-900 truncate">{{ $title }}</h1>
            @if ($subtitle)
                <p class="text-sm text-gray-400 truncate">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-3 shrink-0">
        <div class="hidden md:flex items-center gap-2 rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-500">
            <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-400" />
            <span>{{ ucfirst(now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY · h:mm a')) }}</span>
        </div>

        {{ $slot ?? '' }}

        <livewire:notification-bell />
    </div>
</header>
